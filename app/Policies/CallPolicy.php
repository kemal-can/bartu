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

use App\Models\Call;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any calls.
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
     * Determine whether the user can view the call.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Call $call
     *
     * @return boolean
     */
    public function view(User $user, Call $call)
    {
        return (int) $user->id === (int) $call->user_id;
    }

    /**
     * Determine if the given user can create calls.
     *
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the call.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Call $call
     *
     * @return boolean
     */
    public function update(User $user, Call $call)
    {
        return (int) $user->id === (int) $call->user_id;
    }

    /**
     * Determine whether the user can delete the call.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Call $call
     *
     * @return boolean
     */
    public function delete(User $user, Call $call)
    {
        return (int) $user->id === (int) $call->user_id;
    }
}
