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

namespace App\Support;

use App\Enums\SyncState;
use App\Innoclapps\Models\OAuthAccount;
use App\Contracts\Repositories\CalendarRepository;
use App\Contracts\Repositories\EmailAccountRepository;

trait PurgesOAuthAccount
{
    /**
     * Purge OAuth account and all it's related data
     *
     * @param \App\Innoclapps\Models\OAuthAccount $account
     *
     * @return boolean
     */
    protected function purgeOAuthAccount(OAuthAccount $account)
    {
        $this->stopRelatedEmailAccounts($account, app(EmailAccountRepository::class));
        $this->stopRelatedCalendars($account, app(CalendarRepository::class));

        return $this->repository->delete($account->id);
    }

    /**
     * Stop sync for the given oAuth account connected email accounts
     *
     * @param \App\Innoclapps\Models\OAuthAccount $oAuthAccount
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     *
     * @return void
     */
    protected function stopRelatedEmailAccounts(OAuthAccount $oAuthAccount, EmailAccountRepository $repository)
    {
        $emailAccount = $repository->findByField('access_token_id', $oAuthAccount->id)->first();

        if ($emailAccount) {
            $repository->setSyncState(
                $emailAccount->id,
                SyncState::STOPPED,
                'The connected OAuth account (' . $oAuthAccount->email . ') was deleted, hence, working with this email account cannot be proceeded. Consider removing the email account from the application.'
            );

            $repository->update(['access_token_id' => null], $emailAccount->id);
        }
    }

    /**
     * Stop sync for the given oAuth account connected calendars
     *
     * @param \App\Innoclapps\Models\OAuthAccount $oAuthAccount
     * @param \App\Contracts\Repositories\CalendarRepository $repository
     *
     * @return void
     */
    protected function stopRelatedCalendars(OAuthAccount $oAuthAccount, CalendarRepository $repository)
    {
        if ($calendar = $repository->findByField('access_token_id', $oAuthAccount->id)->first()) {
            $repository->disableSync($calendar);
        }
    }
}
