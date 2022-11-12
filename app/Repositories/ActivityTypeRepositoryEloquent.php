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

use App\Models\ActivityType;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\ActivityTypeRepository;

class ActivityTypeRepositoryEloquent extends AppRepository implements ActivityTypeRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'name' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return ActivityType::class;
    }

    /**
     * Find activity type by given flag
     *
     * @param string $flag
     *
     * @return \App\Models\ActivityType|null
     */
    public function findByFlag(string $flag) : ?ActivityType
    {
        return $this->limit(1)->findByField('flag', $flag)->first();
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
                abort(409, __('activity.type.delete_primary_warning'));
            } elseif (ActivityType::getDefaultType() == $model->getKey()) {
                abort(409, __('activity.type.delete_is_default'));
            } elseif ($model->calendars()->count() > 0) {
                abort(409, __('activity.type.delete_usage_calendars_warning'));
            } elseif ($model->activities()->count() > 0) {
                abort(409, __('activity.type.delete_usage_warning'));
            }

            // We must delete the trashed activities when the type is deleted
            // as we don't have any option to do with the activity except to associate
            // it with other type (if found) but won't be accurate.
            $model->activities()->onlyTrashed()->get()->each(function ($activity) {
                app(ActivityRepository::class)->forceDelete($activity->getKey());
            });
        });
    }
}
