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
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any activities.
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
     * Determine whether the user can view the activity.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Activity $activity
     *
     * @return boolean
     */
    public function view(User $user, Activity $activity)
    {
        if ($user->can('view all activities')) {
            return true;
        }

        if ($user->can('view attends and owned activities')) {
            return (int) $user->id === (int) $activity->user_id ||
            ! is_null(
                $activity->guests->first(
                    fn ($guest) => (int) $guest->guestable_id === (int) $user->getKey() && $user::class === $guest->guestable_type
                )
            );
        }

        return (int) $user->id === (int) $activity->user_id;
    }

    /**
     * Determine if the given user can create activities.
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
     * Determine whether the user can update the activity.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Activity $activity
     *
     * @return boolean|null
     */
    public function update(User $user, Activity $activity)
    {
        if ($user->can('edit own activities')) {
            return (int) $user->id === (int) $activity->user_id;
        }

        if ($user->can('edit all activities')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the activity.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Activity $activity
     *
     * @return boolean|null
     */
    public function delete(User $user, Activity $activity)
    {
        if ($user->can('delete own activities')) {
            return (int) $user->id === (int) $activity->user_id;
        }

        if ($user->can('delete any activity')) {
            return true;
        }
    }

    /**
     * Determine whether the user can mark the activity as complete|incomplete
     *
     * @param \App\Models\User $user
     * @param \App\Models\Activity $activity
     *
     * @return boolean
     */
    public function changeState(User $user, Activity $activity)
    {
        if ($this->update($user, $activity)) {
            return true;
        }

        return (int) $activity->user_id === (int) $user->id;
    }
}
