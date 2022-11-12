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

namespace App\Innoclapps\Facades;

use Illuminate\Support\Facades\Facade;
use App\Innoclapps\Contracts\VoIP\VoIPClient;

class VoIP extends Facade
{
    /**
     * Get the events URL
     *
     * @return string
     */
    public static function eventsUrl()
    {
        // Uses config('app.url') because of NGROK, for testing purposes
        url()->forceRootUrl(config('app.url'));

        return tap(url()->route(config('innoclapps.voip.endpoints.events')), function () {
            url()->forceRootUrl(null);
        });
    }

    /**
     * Get the new call URL
     *
     * @return string
     */
    public static function callUrl()
    {
        // Uses config('app.url') because of NGROK, for testing purposes
        url()->forceRootUrl(config('app.url'));

        return tap(url()->route(config('innoclapps.voip.endpoints.call')), function () {
            url()->forceRootUrl(null);
        });
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return VoIPClient::class;
    }
}
