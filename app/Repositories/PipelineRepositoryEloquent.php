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

use App\Models\Pipeline;
use App\Models\UserOrderedModel;
use App\Models\ModelVisibilityGroup;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;
use App\Contracts\Repositories\PipelineRepository;

class PipelineRepositoryEloquent extends AppRepository implements PipelineRepository
{
    /**
    * Searchable fields
    *
    * @var array
    */
    protected static $fieldSearchable = [
        'name' => 'like',
        'flag' => 'like',
    ];

    /**
    * Specify Model class name
    *
    * @return string
    */
    public static function model()
    {
        return Pipeline::class;
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
        $pipeline = parent::create($attributes);

        $this->saveVisibilityGroup($pipeline, $attributes['visibility_group'] ?? []);

        if (isset($attributes['stages'])) {
            $stageRepository = resolve(StageRepository::class);

            foreach ($attributes['stages'] as $key => $stage) {
                $stageRepository->create(array_merge($stage, [
                    'pipeline_id'   => $pipeline->getKey(),
                    'display_order' => $stage['display_order'] ?? $key + 1,
                ]));
            }
        }

        return $pipeline;
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
        $pipeline = parent::update($attributes, $id);

        if ($attributes['visibility_group'] ?? false) {
            $this->saveVisibilityGroup($pipeline, $attributes['visibility_group']);
        }

        if (isset($attributes['stages'])) {
            $stageRepository = resolve(StageRepository::class);

            foreach ($attributes['stages'] as $key => $stage) {
                if (! isset($stage['display_order'])) {
                    $stage['display_order'] = $key + 1;
                }

                if (isset($stage['id'])) {
                    $stageRepository->update($stage, $stage['id']);
                } else {
                    $stageRepository->create([...$stage, ...['pipeline_id' => $id]]);
                }
            }
        }

        return $pipeline;
    }

    /**
    * Save the display order for the given pipeline
    *
    * @param integer $id
    * @param integer $displayOrder
    *
    * @return void
    */
    public function saveDisplayOrder(int $id, int $displayOrder)
    {
        $pipeline = $this->find($id);

        if (is_null($pipeline->userOrder)) {
            $pipeline->userOrder()->save(
                new UserOrderedModel([
                    'display_order' => $displayOrder,
                    'user_id'       => Auth::id(),
                ])
            );

            return;
        }

        $pipeline->userOrder->update([
            'display_order' => $displayOrder,
        ]);
    }

    /**
    * Save a visilbity group the the pipeline
    *
    * @param \App\Models\Pipeline $pipeline
    * @param array $visibility
    *
    * @return void
    */
    protected function saveVisibilityGroup($pipeline, $visibility)
    {
        if ($pipeline->isPrimary()) {
            return;
        }

        $type = $visibility['type'] ?? Pipeline::$visibilityTypeAll;

        if (is_null($pipeline->visibilityGroup)) {
            $group = new ModelVisibilityGroup(['type' => $type]);
            $pipeline->visibilityGroup()->save($group);
            $pipeline->load('visibilityGroup');
        } else {
            $pipeline->visibilityGroup->update(['type' => $type]);
            $pipeline->visibilityGroup->{Pipeline::$visibilityTypeTeams}()->detach();
            $pipeline->visibilityGroup->{Pipeline::$visibilityTypeUsers}()->detach();
        }

        if ($type !== Pipeline::$visibilityTypeAll) {
            $pipeline->visibilityGroup->{$type}()->attach(
                $visibility['depends_on'] ?? []
            );
        }
    }

    /**
    * Boot the repository
    *
    * @return void
    */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->isPrimary()) {
                abort(409, __('deal.pipeline.delete_primary_warning'));
            } elseif ($model->deals()->count() > 0) {
                abort(409, __('deal.pipeline.delete_usage_warning_deals'));
            }

            // We must delete the trashed deals when the pipeline is deleted
            // as we don't have any option to do with the deal except to associate
            // it with other pipeline (if found) but won't be accurate.
            $model->deals()->onlyTrashed()->get()->each(function ($deal) {
                app(DealRepository::class)->forceDelete($deal->getKey());
            });

            $model->stages()->delete();

            if (! is_null($model->visibilityGroup)) {
                $model->visibilityGroup->delete();
            }

            if (! is_null($model->userOrder)) {
                $model->userOrder->delete();
            }
        });
    }

    /**
    * Find the primary pipeline
    *
    * @return mixed
    */
    public function findPrimary() : Pipeline
    {
        return $this->findByField('flag', Pipeline::PRIMARY_FLAG)->first();
    }

    /**
    * Update pipeline board default sort
    *
    * @param mixed $pipelineId
    * @param mixed $userId
    * @param array $data
    *
    * @return mixed
    */
    public function updateBoardDefaultSort(int $pipelineId, int $userId, array $data)
    {
        return $this->find($pipelineId)->setDefaultSortData($data, $userId);
    }

    /**
    * The relations that are required for the responsee
    *
    * @return array
    */
    protected function eagerLoad()
    {
        return [
            'stages' => function ($query) {
                return $query->orderBy('display_order');
            },
            'visibilityGroup',
            'userOrder',
        ];
    }
}
