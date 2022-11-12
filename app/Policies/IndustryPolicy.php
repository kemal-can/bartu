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
use App\Models\Industry;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndustryPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any industries.
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
     * Determine whether the user can view the industry.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Industry $industry
     *
     * @return boolean
     */
    public function view(User $user, Industry $industry)
    {
        return true;
    }

    /**
     * Determine if the given user can create industry.
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
     * Determine whether the user can update the industry.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Industry $industry
     *
     * @return boolean
     */
    public function update(User $user, Industry $industry)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the industry.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Industry $industry
     *
     * @return boolean
     */
    public function delete(User $user, Industry $industry)
    {
        // Only super admins
        return false;
    }
}
