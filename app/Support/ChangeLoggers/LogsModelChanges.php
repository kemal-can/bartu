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

namespace App\Support\ChangeLoggers;

use Spatie\Activitylog\LogOptions;
use App\Innoclapps\Facades\ChangeLogger;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait LogsModelChanges
{
    use LogsActivity, LogsPivotEvents;

    /**
     * Boot LogsModelChanges trait
     *
     * @return void
     */
    protected static function bootLogsModelChanges()
    {
        static::addLogChange(new CustomFieldsChangesLogger(static::class));

        static::deleted(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->changelog()->delete();
            }
        });
    }

    /**
     * Model has many changelogs
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changelog() : MorphMany
    {
        return $this->morphMany(ActivitylogServiceProvider::determineActivityModel(), 'subject');
    }

    /**
     * Add identifier for the log, used to create components for front-end
     *
     * @param \Spatie\Activitylog\Contracts\Activity $activity
     * @param string $eventName
     *
     * @return void
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        // https://github.com/spatie/laravel-activitylog/issues/1034
        // Why in the world is called twice?
        $activity->identifier ??= $eventName;
        $activity->log_name = ChangeLogger::MODEL_LOG_NAME;
    }

    /**
     * Get the changelog options
     *
     * @return \Spatie\Activitylog\LogOptions
     */
    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(static::$changelogAttributes)
            ->logExcept(static::$changelogAttributeToIgnore)
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->dontSubmitEmptyLogs();
    }

    /**
     * Log dirty attributes on the latest model log
     *
     * @param array $attributes
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public static function logDirtyAttributesOnLatestLog($attributes, $model)
    {
        // We will get the latest created log for the current request, for example
        // if user updated other model fields, there will be log for changes and we will
        // use this log properties to merge the actual custom field changes in the same log
        $latestLog = ChangeLogger::getLatestCreatedLog();

        if (! $latestLog) {
            // In this case, only multi optionable field updated,
            // we will create brand new log for the model
            ChangeLogger::onModel($model, $attributes)
                ->identifier('updated')
                ->log();

            return;
        }

        if ($latestLog->subject->is($model)) {
            $latestLog->properties = collect(array_merge_recursive(
                $latestLog->properties->toArray(),
                $attributes
            ));

            $latestLog->save();
        }
    }
}
