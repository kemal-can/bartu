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

use Illuminate\Support\Str;
use App\Http\Requests\SettingRequest;
use App\Http\Controllers\ApiController;

class SettingsController extends ApiController
{
    /**
     * Get the application settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->response(
            collect(settings()->all())->reject(fn ($value, $name) => Str::startsWith($name, '_'))
        );
    }

    /**
     * Persist the settings in storage
     *
     * @param \App\Http\Requests\SettingRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SettingRequest $request)
    {
        $request->saveSettings();

        return $this->response(settings()->all());
    }
}
