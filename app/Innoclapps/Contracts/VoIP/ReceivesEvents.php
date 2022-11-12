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

namespace App\Innoclapps\Contracts\VoIP;

use Illuminate\Http\Request;

interface ReceivesEvents
{
    /**
     * Set the call events URL
     *
     * @param string $url The URL the client events webhook should be pointed to
     *
     * @return static
     */
    public function setEventsUrl(string $url) : static;

    /**
     * Handle the VoIP service events request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function events(Request $request);
}
