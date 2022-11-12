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
use App\Models\CallOutcome;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallOutcomePolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any outcomes.
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
     * Determine whether the user can view the outcome.
     *
     * @param \App\Models\User $user
     * @param \App\Models\CallOutcome $outcome
     *
     * @return boolean
     */
    public function view(User $user, CallOutcome $outcome)
    {
        return true;
    }

    /**
     * Determine if the given user can create outcome.
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
     * Determine whether the user can update the outcome.
     *
     * @param \App\Models\User $user
     * @param \App\Models\CallOutcome $outcome
     *
     * @return boolean
     */
    public function update(User $user, CallOutcome $outcome)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the outcome.
     *
     * @param \App\Models\User $user
     * @param \App\Models\CallOutcome $outcome
     *
     * @return boolean
     */
    public function delete(User $user, CallOutcome $outcome)
    {
        // Only super admins
        return false;
    }
}
