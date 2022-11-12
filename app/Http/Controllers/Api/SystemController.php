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

use App\Support\SystemInfo;
use App\Innoclapps\LaravelLogReader;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Request;

class SystemController extends ApiController
{
    /**
     * Get the system info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        // System info flag

        return $this->response(new SystemInfo(Request::instance()));
    }

    /**
     * Download the system info
     *
     * @return mixed
     */
    public function downloadInfo()
    {
        // System info download flag

        return Excel::download(new SystemInfo(Request::instance()), 'system-info.xlsx');
    }

    /**
     * Get the application/Laravel logs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs()
    {
        // System logs flag

        return $this->response(
            (new LaravelLogReader(['date' => Request::instance()->date]))->get()
        );
    }
}
