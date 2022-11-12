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

namespace App\Support\Concerns;

use App\Criteria\Activity\OwnActivitiesCriteria;

trait HasActivities
{
    /**
     * Get all of the associated activities for the record.
     *
     * @todo not used, failing because if activity logger activities method, all the models must define this method seprately
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function activities()
    {
        return $this->morphToMany(\App\Models\Activity::class, 'activityable');
    }

    /**
     * A record has incomplete activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function incompleteActivities()
    {
        return $this->activities()->incomplete();
    }

    /**
     * Get the incomplete activities for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function incompleteActivitiesForUser()
    {
        return $this->incompleteActivities()->where(function ($query) {
            return OwnActivitiesCriteria::applyQuery($query);
        });
    }

    /**
     * Get the model next activity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nextActivity()
    {
        return $this->belongsTo(\App\Models\Activity::class, 'next_activity_id');
    }
}
