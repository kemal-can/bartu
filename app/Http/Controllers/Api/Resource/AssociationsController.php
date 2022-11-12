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
use App\Innoclapps\Timeline\Timelineables;
use App\Innoclapps\Criteria\RequestCriteria;
use App\Innoclapps\Resources\Http\ResourceRequest;

class AssociationsController extends ApiController
{
    /**
     * Get the resource associations
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(ResourceRequest $request)
    {
        $this->authorize('view', $request->record());

        $associatedResource = $request->findResource($request->associated);

        abort_if(! $associatedResource?->isAssociateable() || ! $associatedResource->jsonResource(), 404);

        abort_if($request->isForTimeline() &&
            (
                ! Timelineables::hasTimeline($request->record()) ||
                ! Timelineables::isTimelineable($associatedResource->repository()->getModel())
            ), 404);

        $method = $request->isForTimeline() ? 'timelineQuery' : 'associatedIndexQuery';

        $records = $associatedResource->{$method}($request->record())
            ->pushCriteria(RequestCriteria::class)
            ->paginate($request->input('per_page'));

        $associatedResource->jsonResource()::topLevelResource($request->record());

        return $this->response(
            $associatedResource->jsonResource()::collection($records)->toResponse($request)->getData()
        );
    }
}
