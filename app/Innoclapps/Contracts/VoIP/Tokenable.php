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

interface Tokenable
{
    /**
     * Create new client token for the logged in user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function newToken(Request $request);
}
