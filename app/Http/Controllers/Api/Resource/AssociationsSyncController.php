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

use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\Http\ResourceRequest;

class AssociationsSyncController extends ApiController
{
    /**
     * Associate records to resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attach(ResourceRequest $request)
    {
        $this->authorize('update', $request->record());

        $this->validateProvidedResources($request);
        $totalUnauthorized = 0;
        $resources         = $request->keys();

        foreach ($resources as $resourceName) {
            $ids = $request->input($resourceName);

            if (! is_array($ids) || count($ids) === 0) {
                continue;
            }

            $relatedResource = $request->findResource($resourceName);

            $relatedRecords = $this->filterUnauthorizedModels($relatedResource->repository()->findMany($ids));

            if ($relatedRecords->isEmpty()) {
                $totalUnauthorized++;

                continue;
            }

            $result = $request->resource()->repository()
                ->syncWithoutDetaching(
                    $request->resourceId(),
                    $relatedResource->associateableName(),
                    $relatedRecords->modelKeys()
                );

            // When passing only 1 record for associations
            // Show a conflict error message that this record is already associated
            if (count($result['attached']) === 0 &&
                        count($resources) === 1 &&
                        count($ids) == 1) {
                return $this->response(['message' => __('resource.already_associated')], 409);
            }
        }

        if ($totalUnauthorized === count($resources)) {
            abort(403, 'You are not authorized to perform this action.');
        }

        return $this->response($request->toResponse(
            $request->resource()->displayQuery()->find($request->resourceId())
        ));
    }

    /**
     * Dissociate records from resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detach(ResourceRequest $request)
    {
        $this->authorize('update', $request->record());

        $this->validateProvidedResources($request);

        $repository = $request->resource()->repository();

        foreach ($request->keys() as $resourceName) {
            $ids = $request->input($resourceName);

            if (! is_array($ids) || count($ids) === 0) {
                continue;
            }

            $repository->detach(
                $request->resourceId(),
                $request->findResource($resourceName)->associateableName(),
                $ids
            );
        }

        return $this->response('', 204);
    }

    /**
     * Sync records for resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(ResourceRequest $request)
    {
        $this->authorize('update', $record = $request->record());

        $this->validateProvidedResources($request);

        $repository = $request->resource()->repository();

        foreach ($request->keys() as $resourceName) {
            $ids = $request->input($resourceName);

            if (! is_array($ids)) {
                continue;
            }

            $relatedResource = $request->findResource($resourceName);

            if (count($ids) === 0) {
                // No associations, detach all
                $repository->detach(
                    $request->resourceId(),
                    $relatedResource->associateableName(),
                    $this->filterUnauthorizedModels($record->{$relatedResource->associateableName()})->modelKeys()
                );

                continue;
            }

            $repository->sync(
                $request->resourceId(),
                $relatedResource->associateableName(),
                $this->filterUnauthorizedModels($relatedResource->repository()->findMany($ids))->modelKeys()
            );
        }

        return $this->response($request->toResponse(
            $request->resource()->displayQuery()->find($request->resourceId())
        ));
    }

    /**
     * Filter the unauthorized models
     *
     * @param \Illuminate\Support\Collection $models
     *
     * @return \Illuminate\Support\Collection
     */
    protected function filterUnauthorizedModels($models)
    {
        return $models->reject(fn ($model) => Gate::denies('view', $model));
    }

    /**
     * Validate the given resources
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return void
     */
    protected function validateProvidedResources(ResourceRequest $request)
    {
        foreach ($request->keys() as $resource) {
            $relatedResource = $request->findResource($resource);

            if (! $relatedResource ||
                ! $relatedResource->canBeAssociated($request->resource()->name())) {
                abort(
                    400,
                    "The provided resource name \"$resource\" cannot be associated to the {$request->resource()->singularLabel()}"
                );
            }
        }
    }
}
