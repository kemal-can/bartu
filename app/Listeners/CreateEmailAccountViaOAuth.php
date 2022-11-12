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

namespace App\Listeners;

use Illuminate\Http\Request;
use App\Enums\EmailAccountType;
use App\Innoclapps\Facades\OAuthState;
use Illuminate\Support\Facades\Session;
use App\Innoclapps\MailClient\ClientManager;
use App\Innoclapps\MailClient\ConnectionType;
use App\Contracts\Repositories\EmailAccountRepository;

class CreateEmailAccountViaOAuth
{
    /**
     * Initialize new CreateEmailAccountViaOAuth instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(protected EmailAccountRepository $repository, protected Request $request)
    {
    }

    /**
     * Handle Microsoft email account connection finished
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        tap($event->account, function ($oAuthAccount) {
            $account = $this->repository->findByEmail($oAuthAccount->email);

            // Connection not intended for email account
            // Connection can be invoke via /oauth/accounts route or calendar because of re-authentication
            $emailAccountBeingConnected = ! is_null(OAuthState::getParameter('email_account_type'));

            if (! $emailAccountBeingConnected) {
                // We will check if this OAuth account actually exists and if yes,
                // we will make sure that the account is usable and it does not require authentication in database
                // as well that sync is enabled in case stopped previously e.q. because of refresh token
                // in this case, the user won't need to re-authenticate via the email accounts index area again
                if ($account) {
                    $this->makeSureAccountIsUsable($account);
                }

                return;
            }

            if (! $account) {
                $account = $this->createEmailAccount($oAuthAccount);
            } elseif ((string) OAuthState::getParameter('re_auth') !== '1') {
                Session::flash('warning', __('mail.account.already_connected'));
            }

            $this->makeSureAccountIsUsable($account);

            // Update the access_token_id because it's not set in the createEmailAccount method
            $this->repository->unguarded(function ($repository) use ($account, $oAuthAccount) {
                $repository->update(['access_token_id' => $oAuthAccount->id], $account->id);
            });
        });
    }

    /**
     * Make sure that the account is usable
     * Sets requires autentication to false as well enabled sync again if is stopped by system
     *
     * @param \App\Models\EmailAccount $account
     *
     * @return void
     */
    protected function makeSureAccountIsUsable($account)
    {
        $this->repository->setRequiresAuthentication($account->id, false);

        // If the sync is stopped, probably it's because of empty refresh token or
        // failed authenticated for some reason, when reconnected, enable sync again
        if ($account->isSyncStoppedBySystem()) {
            $this->repository->enableSync($account->id);
        }
    }

    /**
     * Create the email account
     *
     * @param \App\Innoclapps\Models\OAuthAccount $oAuthAccount
     *
     * @return \App\Models\EmailAccount
     */
    protected function createEmailAccount($oAuthAccount)
    {
        $payload = [
            'connection_type' => $oAuthAccount->type == 'microsoft' ?
                ConnectionType::Outlook :
                ConnectionType::Gmail,
            'email' => $oAuthAccount->email,
        ];

        $remoteFolders = ClientManager::createClient(
            $payload['connection_type'],
            $oAuthAccount->tokenProvider()
        )->getImap()
            ->getFolders();

        $payload['folders']           = $remoteFolders->toArray();
        $payload['initial_sync_from'] = OAuthState::getParameter('period');

        if ($this->isPersonal()) {
            $payload['user_id'] = $this->request->user()->id;
        }

        return $this->repository->create($payload);
    }

    /**
     * Check whether the account is personal
     *
     * @return boolean
     */
    protected function isPersonal() : bool
    {
        return EmailAccountType::tryFrom(OAuthState::getParameter('email_account_type')) === EmailAccountType::PERSONAL;
    }
}
