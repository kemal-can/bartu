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

namespace App\Models;

use App\Enums\DealStatus;
use App\Resources\Prunable;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Media\HasMedia;
use App\Support\Concerns\HasEmails;
use App\Innoclapps\Concerns\HasUuid;
use App\Innoclapps\Casts\ISO8601Date;
use App\Support\Concerns\HasProducts;
use App\Innoclapps\Concerns\HasCreator;
use App\Support\Concerns\HasActivities;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Timeline\HasTimeline;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\ChangeLoggers\LogsModelChanges;
use App\Innoclapps\Workflow\HasWorkflowTriggers;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Innoclapps\Contracts\Repositories\PinnedTimelineSubjectRepository;

class Deal extends Model implements Presentable
{
    use LogsModelChanges,
        Resourceable,
        HasUuid,
        HasMedia,
        HasCreator,
        HasWorkflowTriggers,
        HasTimeline,
        HasEmails,
        HasFactory,
        HasActivities,
        HasProducts,
        SoftDeletes,
        Prunable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_by',
        'created_at',
        'updated_at',
        'owner_assigned_date',
        'next_activity_id',
        'stage_changed_date',
        'uuid',
    ];

    /**
     * Attributes and relations to log changelog for the model
     *
     * @var array
     */
    protected static $changelogAttributes = [
        '*',
        'user.name',
        'pipeline.name',
    ];

    /**
     * Exclude attributes from the changelog
     *
     * @var array
     */
    protected static $changelogAttributeToIgnore = [
        'updated_at',
        'created_at',
        'created_by',
        'owner_assigned_date',
        'stage_changed_date',
        'swatch_color',
        'board_order',
        'status',
        'won_date',
        'lost_date',
        'lost_reason',
        'next_activity_id',
        // Stage change are handled via custom pivot log events
        'stage_id',
    ];

    /**
     * Provides the relationships for the pivot logger
     *
     * [ 'main' => 'reverse']
     *
     * @return array
     */
    protected static $logPivotEventsOn = [
        'companies' => 'deals',
        'contacts'  => 'deals',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'expected_close_date' => ISO8601Date::class,
        'stage_changed_date'  => 'datetime',
        'owner_assigned_date' => 'datetime',
        'won_date'            => 'datetime',
        'lost_date'           => 'datetime',
        'status'              => DealStatus::class,
        'amount'              => 'decimal:3',
        'stage_id'            => 'int',
        'pipeline_id'         => 'int',
        'user_id'             => 'int',
        'board_order'         => 'int',
        'created_by'          => 'int',
        'web_form_id'         => 'int',
        'next_activity_id'    => 'int',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if ($model->status === DealStatus::open) {
                $model->startStageHistory();
            }
        });

        static::saving(function ($model) {
            if ($model->isDirty('status')) {
                if ($model->status === DealStatus::open) {
                    $model->fill(['won_date' => null, 'lost_date' => null, 'lost_reason' => null]);
                } elseif ($model->status === DealStatus::lost) {
                    $model->fill(['lost_date' => now(), 'won_date' => null]);
                } else {
                    // won status
                    $model->fill(['won_date' => now(), 'lost_date' => null, 'lost_reason' => null]);
                }
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('stage_id')) {
                $model->stage_changed_date = now();
            }

            if (! $model->isDirty('status')) {
                // Guard these attributes when the status is not changed
                foreach (['won_date', 'lost_date'] as $guarded) {
                    if ($model->isDirty($guarded)) {
                        $model->fill([$guarded => $model->getOriginal($guarded)]);
                    }
                }

                // Allow updating the lost reason only when status is lost
                if ($model->status !== DealStatus::lost && $model->isDirty('lost_reason')) {
                    $model->fill(['lost_reason' => $model->getOriginal('lost_reason')]);
                }
            }
        });

        static::updated(function ($model) {
            if ($model->wasChanged('status')) {
                if ($model->status === DealStatus::won || $model->status === DealStatus::lost) {
                    $model->stopLastStageTiming();
                } else {
                    // changed to open
                    $model->startStageHistory();
                }
            }

            if ($model->wasChanged('stage_id') && $model->status === DealStatus::open) {
                $model->recordStageHistory($model->stage_id);
            }
        });
    }

    /**
     * Check whether the falls behind the expected close date
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fallsBehindExpectedCloseDate() : Attribute
    {
        return Attribute::get(function () {
            if (! $this->expected_close_date || $this->status !== DealStatus::open) {
                return false;
            }

            return $this->expected_close_date->isPast();
        });
    }

    /**
     * Mark the deal as lost
     *
     * @param string|null $reason
     *
     * @return static
     */
    public function markAsLost($reason = null) : static
    {
        if ($reason) {
            $this->lost_reason = $reason;
        }

        $this->status = DealStatus::lost;
        $this->save();

        $activity = $this->logStatusChangeActivity('marked_as_lost', ['reason' => $this->lost_reason]);

        $attrs = [$this->id, static::class, $activity->getKey(), $activity::class];
        app(PinnedTimelineSubjectRepository::class)->pin(...$attrs);

        return $this;
    }

    /**
     * Mark the deal as wont
     *
     * @return static
     */
    public function markAsWon() : static
    {
        $this->status = DealStatus::won;
        $this->save();

        $this->logStatusChangeActivity('marked_as_won');

        return $this;
    }

    /**
     * Mark the deal as open
     *
     * @return static
     */
    public function markAsOpen() : static
    {
        $this->status = DealStatus::open;
        $this->save();
        $this->logStatusChangeActivity('marked_as_open');

        return $this;
    }

    /**
    * Log status change activity for the deal
    *
    * @param array $attrs
    *
    * @return \App\Innoclapps\Models\Changelog
    */
    protected function logStatusChangeActivity($langKey, $attrs = [])
    {
        return ChangeLogger::onModel($this, [
              'lang' => [
                  'key'   => 'deal.timeline.' . $langKey,
                  'attrs' => $attrs,
              ],
          ])->log();
    }

    /**
     * Get all of the associated activities for the record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function activities()
    {
        return $this->morphToMany(\App\Models\Activity::class, 'activityable');
    }

    /**
     * Get the pipeline that belongs to the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline()
    {
        return $this->belongsTo(\App\Models\Pipeline::class);
    }

    /**
     * Get the stage that belongs to the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Get the stages history the deal has been.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stagesHistory()
    {
        return $this->belongsToMany(Stage::class, 'deal_stages_history')
            ->withPivot(['id', 'entered_at', 'left_at'])
            ->using(\App\Models\StageHistory::class)
            ->orderBy('entered_at', 'desc')
            ->as('history');
    }

    /**
     * Get the stages history with time in
     *
     * @return \Illuminate\Support\Collection
     */
    public function timeInStages()
    {
        return $this->stagesHistory->groupBy('id')->map(function ($stages) {
            return $stages->reduce(function ($carry, $stage) {
                // If left_at is null, is using the current time
                $carry += $stage->history->entered_at->diffInSeconds($stage->history->left_at);

                return $carry;
            }, 0);
        });
    }

    /**
     * Start start history from the deal current stage
     *
     * @return static
     */
    public function startStageHistory() : static
    {
        $this->recordStageHistory($this->stage_id);

        return $this;
    }

    /**
     * Get the deal last stage history
     *
     * @return \App\Models\Stage
     */
    public function lastStageHistory()
    {
        return $this->stagesHistory()->first();
    }

    /**
     * Stop the deal last stage timing
     *
     * @return static
     */
    public function stopLastStageTiming() : static
    {
        $latest = $this->lastStageHistory();

        if ($latest && is_null($latest['history']['left_at'])) {
            $this->stagesHistory()
                ->wherePivot('id', $latest->history->id)
                ->updateExistingPivot($latest->history->stage_id, ['left_at' => now()]);
        }

        return $this;
    }

    /**
     * Record stage history
     *
     * @param int $stageId The new stage id
     *
     * @return static
     */
    public function recordStageHistory(int $stageId) : static
    {
        $this->stopLastStageTiming();
        $this->stagesHistory()->attach($stageId, ['entered_at' => now()]);

        return $this;
    }

    /**
     * Get all of the contacts that are associated with the deal
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function contacts()
    {
        return $this->morphedByMany(\App\Models\Contact::class, 'dealable')
            ->withTimestamps()
            ->orderBy('dealables.created_at');
    }

    /**
     * Get all of the companies that are associated with the deal
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function companies()
    {
        return $this->morphedByMany(\App\Models\Company::class, 'dealable')
            ->withTimestamps()
            ->orderBy('dealables.created_at');
    }

    /**
     * Get all of the notes for the deal
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function notes()
    {
        return $this->morphToMany(\App\Models\Note::class, 'noteable');
    }

    /**
     * Get all of the calls for the deal
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function calls()
    {
        return $this->morphToMany(\App\Models\Call::class, 'callable');
    }

    /**
     * Get the deal owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the model display name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function displayName() : Attribute
    {
        return Attribute::get(fn () => $this->name);
    }

    /**
     * Get the URL path
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::get(fn () => "/deals/{$this->id}");
    }

    /**
     * Provide the total column to be updated whenever the billable is updated
     *
     * @return string
     */
    public function totalColumn() : string
    {
        return 'amount';
    }
}
