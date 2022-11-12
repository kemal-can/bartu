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

use App\Innoclapps\Facades\Fields;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\Http\ResourceRequest;

class FieldController extends ApiController
{
    /**
     * Get the resource create fields
     *
     * @param \App\Http\Requests\ResourceRequest $request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ResourceRequest $request)
    {
        return $this->response(
            Fields::resolveCreateFieldsForDisplay($request->resourceName())
        );
    }

    /**
     * Get the resource update fields
     *
     * @param \App\Http\Requests\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ResourceRequest $request)
    {
        $request->resource()->setModel($request->record());

        return $this->response(
            Fields::resolveUpdateFieldsForDisplay($request->resourceName())
        );
    }

    /**
     * Get the resource detail fields
     *
     * @param \App\Http\Requests\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(ResourceRequest $request)
    {
        $request->resource()->setModel($request->record());

        return $this->response(
            Fields::resolveDetailFieldsForDisplay($request->resourceName())
        );
    }
}
