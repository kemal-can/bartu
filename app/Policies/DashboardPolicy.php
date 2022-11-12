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
use App\Innoclapps\Models\Dashboard;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dashboard.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Dashboard $dashboard
     *
     * @return boolean
     */
    public function view(User $user, Dashboard $dashboard)
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can update the dashboards.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Dashboard $dashboard
     *
     * @return boolean
     */
    public function update(User $user, Dashboard $dashboard)
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }

    /**
     * Determine whether the user can delete the dashboard.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Dashboard $dashboard
     *
     * @return boolean
     */
    public function delete(User $user, Dashboard $dashboard)
    {
        return (int) $user->id === (int) $dashboard->user_id;
    }
}
