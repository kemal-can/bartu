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
use App\Innoclapps\Models\OAuthAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class OAuthAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the account.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Model\OAuthAccount $account
     *
     * @return boolean
     */
    public function view(User $user, OAuthAccount $account)
    {
        return (int) $account->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the account.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Model\OAuthAccount $account
     *
     * @return boolean
     */
    public function delete(User $user, OAuthAccount $account)
    {
        return (int) $user->id === (int) $account->user_id;
    }
}
