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
use App\Innoclapps\Criteria\OnlyTrashedCriteria;
use App\Innoclapps\Resources\Http\TrashedResourcefulRequest;

class TrashedController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Innoclapps\Resources\Http\TrashedResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(TrashedResourcefulRequest $request)
    {
        $this->authorize('viewAny', $request->resource()->model());

        return $this->response(
            $request->toResponse(
                $request->resource()
                    ->resourcefulHandler($request)
                    ->onlyTrashed()
                    ->index()
            )
        );
    }

    /**
     * Perform search on the trashed resource.
     *
     * @param \App\Innoclapps\Resources\Http\TrashedResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(TrashedResourcefulRequest $request)
    {
        $resource = $request->resource();

        abort_if(! $resource::searchable(), 404);

        if (empty($request->q)) {
            return $this->response([]);
        }

        $repository = $resource::repository()
            ->pushCriteria(RequestCriteria::class)
            ->pushCriteria(OnlyTrashedCriteria::class);

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

    /**
     * Display resource record.
     *
     * @param \App\Innoclapps\Resources\Http\TrashedResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TrashedResourcefulRequest $request)
    {
        $this->authorize('view', $request->record());

        return $this->response(
            $request->toResponse(
                $request->resource()
                    ->resourcefulHandler($request)
                    ->onlyTrashed()
                    ->show($request->resourceId())
            )
        );
    }

    /**
     * Remove resource record from storage.
     *
     * @param \App\Innoclapps\Resources\Http\TrashedResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TrashedResourcefulRequest $request)
    {
        $this->authorize('delete', $request->record());

        $content = $request->resource()
            ->resourcefulHandler($request)
            ->onlyTrashed()
            ->forceDelete($request->resourceId());

        return $this->response($content, empty($content) ? 204 : 200);
    }

    /**
    * Restore the soft deleted record.
    *
    * @param \App\Innoclapps\Resources\Http\TrashedResourcefulRequest $request
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function restore(TrashedResourcefulRequest $request)
    {
        $this->authorize('view', $request->record());

        $content = $request->resource()
            ->resourcefulHandler($request)
            ->onlyTrashed()
            ->restore($request->resourceId());

        return $this->response($content, empty($content) ? 204 : 200);
    }
}
