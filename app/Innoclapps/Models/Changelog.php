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

namespace App\Innoclapps\Models;

use App\Innoclapps\Timeline\Timelineable;
use Spatie\Activitylog\Models\Activity as SpatieActivityLog;

class Changelog extends SpatieActivityLog
{
    use Timelineable;

    /**
     * Latests saved activity
     *
     * @var null|\App\Innoclapps\Models\Changelog
     */
    public static $latestSavedLog;

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        /**
         * Automatically set the causer name if the logged in
         * user is set and no causer name provided
         */
        static::creating(function ($model) {
            if (! $model->causer_name) {
                $model->causer_name = auth()->user()?->name;
            }
        });

        static::created(function ($model) {
            static::$latestSavedLog = $model;
        });
    }

    /**
     * Get the relation name when the model is used as activity
     *
     * @return string
     */
    public function getTimelineRelation()
    {
        return 'changelog';
    }

    /**
     * Get the activity front-end component
     *
     * @return string
     */
    public function getTimelineComponent()
    {
        return $this->identifier;
    }
}
