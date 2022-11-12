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
use App\Models\EmailAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the email account.
     *
     * @param \App\Models\User $user
     * @param \App\Models\EmailAccount $account
     *
     * @return boolean
     */
    public function view(User $user, EmailAccount $account)
    {
        if ($account->isShared()) {
            return $user->can('access shared inbox');
        }

        return (int) $user->id === (int) $account->user_id;
    }

    /**
     * Determine whether the user can update the email account.
     *
     * @param \App\Models\User $user
     * @param \App\Models\EmailAccount $account
     *
     * @return boolean
     */
    public function update(User $user, EmailAccount $account)
    {
        return $this->authorizeUpdateAndDelete($user, $account);
    }

    /**
     * Determine whether the user can delete the email account.
     *
     * @param \App\Models\User $user
     * @param \App\Models\EmailAccount $account
     *
     * @return boolean
     */
    public function delete(User $user, EmailAccount $account)
    {
        // We check if the account not requires auth before deleting because
        // the user must re-authenticate the account in order to delete
        // This will allow in the observer, to revoke the access token
        // so if in case the next time the user want to re-connect the account to
        //return the refresh token as the refresh token is returned only on the first request
        if ($account->requires_auth) {
            return false;
        }

        return $this->authorizeUpdateAndDelete($user, $account);
    }

    /**
     * General account check
     *
     * @param \App\Models\User $user
     * @param \App\Models\EmailAccount $account
     *
     * @return boolean
     */
    protected function authorizeUpdateAndDelete($user, $account)
    {
        if ($account->isShared()) {
            return $user->isSuperAdmin();
        }

        return (int) $user->id === (int) $account->user_id;
    }
}
