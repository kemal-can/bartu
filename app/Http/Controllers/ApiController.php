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

use Illuminate\Support\Facades\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiController extends Controller
{
    /**
     * General API Response
     *
     * @see  \Illuminate\Contracts\Routing\ResponseFactory
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data = [], $status = 200, array $headers = [], $options = 0)
    {
        // https://stackoverflow.com/questions/57604784/laravel-resource-collection-paginate-json-response-error - for paginated collections
        if ($data instanceof AnonymousResourceCollection) {
            $data = $data->toResponse(Request::instance())->getData();
        }

        return response()->json($data, $status, $headers, $options);
    }
}
