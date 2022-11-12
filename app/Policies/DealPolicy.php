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
use App\Models\Deal;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any deals.
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
     * Determine whether the user can view the deal.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Deal $deal
     *
     * @return boolean
     */
    public function view(User $user, Deal $deal)
    {
        if ($user->can('view all deals')) {
            return true;
        }

        return (int) $deal->user_id === (int) $user->id;
    }

    /**
     * Determine if the given user can create deals.
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
     * Determine whether the user can update the deal.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Deal $deal
     *
     * @return boolean|null
     */
    public function update(User $user, Deal $deal)
    {
        if ($user->can('edit own deals')) {
            return (int) $user->id === (int) $deal->user_id;
        }

        if ($user->can('edit all deals')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the deal.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Deal $deal
     *
     * @return boolean|null
     */
    public function delete(User $user, Deal $deal)
    {
        if ($user->can('delete own deals')) {
            return (int) $user->id === (int) $deal->user_id;
        }

        if ($user->can('delete any deal')) {
            return true;
        }
    }
}
