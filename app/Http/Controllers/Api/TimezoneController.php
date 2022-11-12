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

namespace App\Http\Controllers\Api;

use App\Innoclapps\Facades\Timezone;
use App\Http\Controllers\ApiController;

class TimezoneController extends ApiController
{
    /**
     * List all timezones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        return $this->response(Timezone::toArray());
    }
}
