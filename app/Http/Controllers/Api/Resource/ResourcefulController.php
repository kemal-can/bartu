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

use Illuminate\Support\Str;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\Http\ResourcefulRequest;
use App\Innoclapps\Resources\Http\CreateResourceRequest;
use App\Innoclapps\Resources\Http\UpdateResourceRequest;

class ResourcefulController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Innoclapps\Resources\Http\ResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ResourcefulRequest $request)
    {
        // Resourceful index flag
        $this->authorize('viewAny', $request->resource()->model());

        return $this->response(
            $request->toResponse(
                $request->resource()->resourcefulHandler($request)->index()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Innoclapps\Resources\Http\CreateResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateResourceRequest $request)
    {
        // Resourceful store flag
        $this->authorize('create', $request->resource()->model());

        $record = $request->resource()->displayQuery()->find(
            $request->resource()->resourcefulHandler($request)->store()->getKey()
        );

        // Set that this record was recently created as the property value is lost
        // because we are re-querying the record again after creation
        $record->wasRecentlyCreated = true;

        return $this->response(
            $request->toResponse($record),
            201
        );
    }

    /**
     * Display resource record.
     *
     * @param \App\Innoclapps\Resources\Http\ResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ResourcefulRequest $request)
    {
        // Resourceful show flag
        $this->authorize('view', $request->record());

        $record = $request->resource()
            ->resourcefulHandler($request)
            ->show($request->resourceId())
            ->loadMissing(
                ! is_array($request->with) ? $this->withFromString($request->get('with', '')) : $request->with
            );

        return $this->response(
            $request->toResponse($record)
        );
    }

    /**
     * Update resource record in storage.
     *
     * @param \App\Innoclapps\Resources\Http\UpdateResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateResourceRequest $request)
    {
        // Resourceful update flag
        $this->authorize('update', $request->record());

        $request->resource()->resourcefulHandler($request)->update($request->resourceId());

        $record = $request->resource()->displayQuery()
            ->with(! is_array($request->with) ? $this->withFromString($request->get('with', '')) : $request->with)
            ->find($request->resourceId());

        return $this->response(
            $request->toResponse($record)
        );
    }

    /**
     * Remove resource record from storage.
     *
     * @param \App\Innoclapps\Resources\Http\ResourcefulRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ResourcefulRequest $request)
    {
        // Resourceful destroy flag
        $this->authorize('delete', $request->record());

        $content = $request->resource()->resourcefulHandler($request)->destroy($request->resourceId());

        return $this->response($content, empty($content) ? 204 : 200);
    }

    /**
     * Get with from the given string
     *
     * @param string $with
     *
     * @return array
     */
    protected function withFromString(string $with) : array
    {
        return Str::of($with)->explode(';')->filter()->all();
    }
}
