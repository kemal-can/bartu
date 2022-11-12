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
use App\Models\Source;
use Illuminate\Auth\Access\HandlesAuthorization;

class SourcePolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any sources.
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
     * Determine whether the user can view the source.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Source $source
     *
     * @return boolean
     */
    public function view(User $user, Source $source)
    {
        return true;
    }

    /**
     * Determine if the given user can create source.
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
     * Determine whether the user can update the source.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Source $source
     *
     * @return boolean
     */
    public function update(User $user, Source $source)
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the source.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Source $source
     *
     * @return boolean
     */
    public function delete(User $user, Source $source)
    {
        // Only super admins
        return false;
    }
}
