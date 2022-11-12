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

use App\Models\User;
use App\Models\ModelVisibilityGroup;

trait RestrictsModelVisibility
{
    /**
     * @var string
     */
    public static $visibilityTypeTeams = 'teams';

    /**
     * @var string
     */
    public static $visibilityTypeUsers = 'users';

    /**
     * @var string
     */
    public static $visibilityTypeAll = 'all';

    /**
     * Get the mode visibility group
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function visibilityGroup()
    {
        return $this->morphOne(ModelVisibilityGroup::class, 'visibilityable');
    }

    /**
     * Get teams visiblity query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     *
     * @return string
     */
    protected function getTeamsVisibilityQuery($query, $user) : string
    {
        $raw   = '';
        $query = clone $query;

        $query->whereHas('visibilityGroup.teams', function ($q) use (&$raw, $user) {
            $raw = $this->getVisibilitySql($q->whereIn('dependable_id', $user->teams->pluck('id')->all()));
        });

        return $raw;
    }

    /**
     * Get users visiblity query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     *
     * @return string
     */
    protected function getUsersVisibilityQuery($query, $user)
    {
        $raw   = '';
        $query = clone $query;

        $query->whereHas('visibilityGroup.users', function ($q) use (&$raw, $user) {
            $raw = $this->getVisibilitySql($q->whereIn('dependable_id', [$user->getKey()]));
        });

        return $raw;
    }

    /**
     * Apply the scope to the given Eloquent query
     *
     * @param \Illuminate\Database\Eloquent\Query $query
     * @param \App\Models\User $user
     *
     * @return void
     */
    public function scopeVisible($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $query->whereHas('visibilityGroup', function ($q) use ($user, $query) {
            $q->whereRaw('CASE
                WHEN (type = "' . static::$visibilityTypeTeams . '") THEN exists (' . $this->getTeamsVisibilityQuery($query, $user) . ')
                WHEN (type = "' . static::$visibilityTypeUsers . '") THEN exists (' . $this->getUsersVisibilityQuery($query, $user) . ')
                ELSE 1=1
            END');
        })->orWhereDoesntHave('visibilityGroup');
    }

    /**
     * Get SQL for visibliity
     *
     * @param \Illuminate\Database\Eloquent\Query $query
     *
     * @return string
     */
    protected function getVisibilitySql($query)
    {
        $bindings = $query->getBindings();

        return preg_replace_callback('/\?/', function ($match) use (&$bindings, $query) {
            return $query->getConnection()->getPdo()->quote(array_shift($bindings));
        }, $query->toSql());
    }
}
