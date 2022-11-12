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
use App\Models\ActivityType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityTypePolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any types.
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
     * Determine whether the user can view the type.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ActivityType $type
     *
     * @return boolean
     */
    public function view(User $user, ActivityType $type)
    {
        return true;
    }

    /**
     * Determine if the given user can create type.
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
     * Determine whether the user can update the type.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ActivityType $type
     *
     * @return boolean
     */
    public function update(User $user, ActivityType $type)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the type.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ActivityType $type
     *
     * @return boolean
     */
    public function delete(User $user, ActivityType $type)
    {
        // Only super admins
        return false;
    }
}
