<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Repositories;

use App\Models\Deal;
use App\Enums\DealStatus;
use App\Events\DealMovedToStage;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Repository\SoftDeletes;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CallRepository;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\NoteRepository;
use App\Contracts\Repositories\StageRepository;
use App\Contracts\Repositories\BillableRepository;

class DealRepositoryEloquent extends AppRepository implements DealRepository
{
    use SoftDeletes;

    /**
     * @var \App\Contracts\Repositories\StageRepository
     */
    protected $stageRepository;

    /**
     * Stages cache
     *
     * @var array
     */
    protected array $stagesCache = [];

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        // For searching deals in a specific pipeline e.q. ?q=query&pipeline_id=1
        'pipeline_id',
        'name' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Deal::class;
    }

    /**
    * Save a new entity in repository
    *
    * @param array $attributes
    *
    * @return mixed
    */
    public function create(array $attributes)
    {
        // Allow providing status e.q. via API or Zapier on create
        // If not provided, default is open, we need to set it to open
        // so Laravel can fill into the model and later then the status
        // can be used e.q. on workflows, as if we don't fill the status, will be false
        // regardless if $table->unsignedInteger('status')->index()->default(1); is default 1
        $attributes['status'] ??= DealStatus::open;

        if (is_string($attributes['status'])) {
            $attributes['status'] = DealStatus::find($attributes['status']);
        }

        return parent::create($attributes);
    }

    /**
     * Perform model insert operation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     *
     * @return void
     */
    protected function performInsert($model, $attributes)
    {
        $stage = $this->getStage($attributes['stage_id']);

        // When creating deal, if the pipeline is not provided use the stage pipeline_id
        // additionally, we will check if the actual provided pipeline is not equal with the provided stage
        // pipeline, when such case, we will just use the stage pipeline
        if (! isset($attributes['pipeline_id']) || $attributes['pipeline_id'] != $stage->pipeline_id) {
            $model->fill(['pipeline_id' => $stage->pipeline_id]);
        }

        parent::performInsert($model, $attributes);

        // API usage only, e.q. allow deal to be created with products
        if (isset($attributes['billable'])) {
            app(BillableRepository::class)->save($attributes['billable'], $model);
        }
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        if (array_key_exists('stage_id', $attributes)) {
            $originalStage = $this->columns('stage_id')->find($id)->stage;
        }

        if (is_string($attributes['status'] ?? null)) {
            $attributes['status'] = DealStatus::find($attributes['status']);
        }

        $model = parent::update($attributes, $id);

        if ($model->wasChanged('stage_id')) {
            event(new DealMovedToStage($model, $originalStage));
        }

        return $model;
    }

    /**
     * Perform model update operation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $attributes
     *
     * @return void
     */
    protected function performUpdate($model, $attributes)
    {
        // Update the pipeline_id from the actual stage pipeline if the
        // deal pipeline is not the same as the stage pipeline
        if ($this->pipelineIsNotEqualToStagePipeline($model->pipeline_id, $attributes)) {
            $model->fill(['pipeline_id' => $this->getStage($attributes['stage_id'])->pipeline_id]);
        }

        if (array_key_exists('status', $attributes) && $attributes['status'] != $model->getOriginal('status')) {
            unset($model->status);
            $this->changeStatus($attributes['status'], $model, $attributes['lost_reason'] ?? null);
        }

        parent::performUpdate($model, $attributes);
    }

    /**
     * Check whether the deal pipeline is equal to the stage pipeline
     * from the provided deal and attributes
     *
     * @param int $originalPipelineId
     * @param array $attributes
     *
     * @return boolean
     */
    protected function pipelineIsNotEqualToStagePipeline($originalPipelineId, $attributes)
    {
        if (isset($attributes['stage_id']) && isset($attributes['pipeline_id'])) {
            return $this->getStage($attributes['stage_id'])->pipeline_id != $attributes['pipeline_id'];
        }

        if (isset($attributes['stage_id'])) {
            return $this->getStage($attributes['stage_id'])->pipeline_id != $originalPipelineId;
        }

        return false;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::restoring(function ($model) {
            $model->logToAssociatedRelationsThatRelatedInstanceIsRestored(['contacts', 'companies']);
        });

        static::deleting(function ($model, $repository) {
            if ($model->isForceDeleting()) {
                $repository->purge($model);
            } else {
                $model->logToAssociatedRelationsThatRelatedInstanceIsTrashed(['contacts', 'companies'], [
                     'key'   => 'deal.timeline.associate_trashed',
                     'attrs' => ['dealName' => $model->display_name],
                 ]);
            }
        });
    }

    /**
     * Purge the deal data
     *
     * @param \App\Models\Deal $deal
     *
     * @return void
     */
    protected function purge($deal)
    {
        foreach (['emails', 'contacts', 'companies', 'activities'] as $relation) {
            tap($deal->{$relation}(), function ($query) {
                if ($query->getModel()->usesSoftDeletes()) {
                    $query->withTrashed();
                }

                $query->detach();
            });
        }

        if ($billableId = $deal->billable?->id) {
            resolve(BillableRepository::class)->delete($billableId);
        }

        $deal->loadMissing(['notes', 'calls']);
        resolve(NoteRepository::class)->delete($deal->notes);
        resolve(CallRepository::class)->delete($deal->calls);
    }

    /**
     * Change the deal status
     *
     * @param \App\Enums\DealStatus $status
     * @param \App\Models\Deal|integer $deal
     * @param string|null $lostReason
     *
     * @return void
     */
    public function changeStatus(DealStatus $status, Deal|int $deal, ?string $lostReason = null)
    {
        $model = $deal instanceof Deal ? $deal : $this->find($deal);

        return match ($status) {
            DealStatus::won  => $model->markAsWon(),
            DealStatus::lost => $model->markAsLost($lostReason),
            DealStatus::open => $model->markAsOpen(),
        };
    }

    /**
     * Get cached stage
     *
     * @param int $id
     *
     * @return \App\Models\Stage
     */
    protected function getStage($id)
    {
        if (! isset($this->stagesCache[$id])) {
            $this->stagesCache[$id] = $this->getStageRepository()->find($id);
        }

        return $this->stagesCache[$id];
    }

    /**
     * Boot the repository
     *
     * @return \App\Contracts\Repositories\StageRepository
     */
    public function getStageRepository()
    {
        if (! is_null($this->stageRepository)) {
            return $this->stageRepository;
        }

        return $this->stageRepository = resolve(StageRepository::class);
    }

    /**
     * Log status change activity for the deal
     *
     * @param \App\Models\Deal $deal
     * @param string $status
     * @param array $attrs
     *
     * @return \App\Innoclapps\Models\Changelog
     */
    public function logStatusChangeActivity($deal, $status, $attrs = [])
    {
        return ChangeLogger::onModel($deal, [
            'lang' => [
                'key'   => 'deal.timeline.' . $status,
                'attrs' => $attrs,
            ],
        ])->log();
    }

    /**
     * The relations that are required for the responsee
     *
     * @return array
     */
    protected function eagerLoad()
    {
        $this->withCount(['calls', 'notes']);

        return [
                'changelog',
                'changelog.pinnedTimelineSubjects',
                'stagesHistory',
                'media',
                'contacts.phones', // for calling
                'companies.phones', // for calling
                'billable',
                'billable.products' => function ($query) {
                    return $query->orderBy('display_order');
                },
                'billable.products.originalProduct',
                'billable.products.billable',
                'pipeline.stages' => function ($query) {
                    return $query->orderBy('display_order');
                },
            ];
    }
}
