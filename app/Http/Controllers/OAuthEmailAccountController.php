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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\EmailAccountType;
use App\Innoclapps\Facades\OAuthState;
use App\Innoclapps\OAuth\OAuthManager;

class OAuthEmailAccountController extends Controller
{
    /**
     * OAuth connect email account
     *
     * @param string $type shared|personal
     * @param string $providerName
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\OAuth\OAuthManager $manager
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect($type, $providerName, Request $request, OAuthManager $manager)
    {
        abort_if(
            ! $request->user()->isSuperAdmin() && EmailAccountType::from($type) === EmailAccountType::SHARED,
            403,
            'Unauthorized action.'
        );

        return redirect($manager->createProvider($providerName)
            ->getAuthorizationUrl(['state' => $this->createState($request, $type, $manager)]));
    }

    /**
     * Create state
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type
     * @param \App\Innoclapps\OAuth\OAuthManager $manager
     *
     * @return string
     */
    protected function createState($request, $type, $manager)
    {
        return OAuthState::putWithParameters([
            'return_url'         => '/mail/accounts?viaOAuth=true',
            'period'             => $request->period,
            'email_account_type' => $type,
            're_auth'            => $request->re_auth,
            'key'                => $manager->generateRandomState(),
        ]);
    }
}
