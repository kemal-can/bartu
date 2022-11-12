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

use App\Http\Resources\FilterResource;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Repositories\FilterRepository;

class FilterController extends ApiController
{
    /**
     * Get the resource saved filters
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ResourceRequest $request, FilterRepository $repository)
    {
        $filters = $repository->forUser(
            $request->resourceName(),
            $request->user()->id
        );

        return $this->response(
            FilterResource::collection($filters)
        );
    }

    /**
     * Get the resource available filters rules
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rules(ResourceRequest $request)
    {
        return $this->response($request->resource()->filtersForResource($request));
    }
}
