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
use App\Innoclapps\Facades\OAuthState;
use App\Innoclapps\OAuth\OAuthManager;

class OAuthCalendarController extends Controller
{
    /**
     * OAuth connect email account
     *
     * @param string $providerName
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\OAuth\OAuthManager $manager
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect($providerName, Request $request, OAuthManager $manager)
    {
        return redirect($manager->createProvider($providerName)
            ->getAuthorizationUrl(['state' => $this->createState($request, $manager)]));
    }

    /**
     * Create state
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\OAuth\OAuthManager $manager
     *
     * @return string
     */
    protected function createState($request, $manager)
    {
        return OAuthState::putWithParameters([
            'return_url' => '/calendar/sync?viaOAuth=true',
            're_auth'    => $request->re_auth,
            'key'        => $manager->generateRandomState(),
        ]);
    }
}
