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
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any users.
    *
    * @param \App\Models\User  $currentUser
    *
    * @return boolean
    */
    public function viewAny(User $currentUser)
    {
        return true;
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param \App\Models\User $currentUser
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function view(User $currentUser, User $user)
    {
        return true;
    }

    /**
     * Determine if the given user can create users.
     *
     * @param \App\Models\User $currentUser
     *
     * @return boolean
     */
    public function create(User $currentUser)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the company.
     *
     * @param \App\Models\User $currentUser
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function update(User $currentUser, User $user)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the company.
     *
     * @param \App\Models\User $currentUser
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function delete(User $currentUser, User $user)
    {
        // Only super admins
        return false;
    }
}
