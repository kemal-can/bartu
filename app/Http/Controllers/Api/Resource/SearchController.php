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

use App\Http\Controllers\ApiController;
use App\Innoclapps\Criteria\RequestCriteria;
use App\Innoclapps\Resources\Http\ResourceRequest;

class SearchController extends ApiController
{
    /**
     * Perform search for a resource.
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(ResourceRequest $request)
    {
        $resource = tap($request->resource(), function ($resource) {
            abort_if(! $resource::searchable(), 404);
        });

        if (empty($request->q)) {
            return $this->response([]);
        }

        $repository = $resource::repository()
            ->pushCriteria(RequestCriteria::class);

        if ($ownCriteria = $resource->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        [$with, $withCount] = $resource::getEagerloadableRelations($resource->resolveFields());

        $repository->withCount($withCount->all())
            ->with($with->all());

        return $this->response(
            $request->toResponse(
                $resource->applyOrder($repository)->all()
            )
        );
    }
}
