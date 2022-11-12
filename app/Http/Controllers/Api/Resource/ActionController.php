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

namespace App\Http\Controllers\Api\Resource;

use App\Http\Requests\ActionRequest;
use App\Http\Controllers\ApiController;

class ActionController extends ApiController
{
    /**
     * Run resource action.
     *
     * @param string $action Action uri key
     * @param \App\Http\Requests\ActionRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($action, ActionRequest $request)
    {
        $request->validateFields();

        return $request->run();
    }
}
