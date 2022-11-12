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

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\ApiController;
use App\Innoclapps\Facades\Permissions;

class PermissionController extends ApiController
{
    /**
     * Get all registered application permissions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        Permissions::createMissing();

        return $this->response([
            'grouped' => Permissions::grouped(),
            'all'     => Permissions::all(),
        ]);
    }
}
