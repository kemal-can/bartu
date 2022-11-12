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

use App\Models\Team;

trait HasTeams
{
    /**
     * Get all of the teams the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the user belongs to the given team.
     *
     * @param int|\App\Models\Team $team
     *
     * @return bool
     */
    public function belongsToTeam(int|Team $team)
    {
        return $this->teams->contains(
            fn ($t) => $t->id === (is_int($team) ? $team : $team->id)
        );
    }
}
