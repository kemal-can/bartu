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

namespace App\Policies;

use App\Models\User;
use App\Models\Pipeline;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelinePolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any pipelines.
    *
    * @param \App\Models\User  $user
    *
    * @return boolean
    */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the pipeline.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Pipeline $pipeline
     *
     * @return boolean
     */
    public function view(User $user, Pipeline $pipeline)
    {
        if (! $pipeline->visibilityGroup || $pipeline->visibilityGroup->type === $pipeline::$visibilityTypeAll) {
            return true;
        }

        if ($pipeline->visibilityGroup->type === $pipeline::$visibilityTypeTeams) {
            foreach ($pipeline->visibilityGroup->{$pipeline::$visibilityTypeTeams} as $team) {
                if ($user->belongsToTeam($team->getKey())) {
                    return true;
                }
            }
        }

        if ($pipeline->visibilityGroup->type === $pipeline::$visibilityTypeUsers) {
            return in_array(
                $user->getKey(),
                $pipeline->visibilityGroup->{$pipeline::$visibilityTypeUsers}->modelKeys()
            );
        }
    }

    /**
     * Determine if the given user can create pipeline.
     *
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function create(User $user)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the pipeline.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Pipeline $pipeline
     *
     * @return boolean
     */
    public function update(User $user, Pipeline $pipeline)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the pipeline.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Pipeline $pipeline
     *
     * @return boolean
     */
    public function delete(User $user, Pipeline $pipeline)
    {
        // Only super admins
        return false;
    }
}
