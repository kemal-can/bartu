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
use App\Innoclapps\Models\Filter;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the filter.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Filter $filter
     *
     * @return boolean
     */
    public function update(User $user, Filter $filter)
    {
        return (int) $filter->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the filter.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Filter $filter
     *
     * @return boolean
     */
    public function delete(User $user, Filter $filter)
    {
        return (int) $filter->user_id === (int) $user->id;
    }
}
