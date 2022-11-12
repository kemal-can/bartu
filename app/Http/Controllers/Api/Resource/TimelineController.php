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

use Illuminate\Http\Request;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Resources\Resource;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ChangelogResource;
use App\Innoclapps\Timeline\Timelineables;
use App\Innoclapps\Models\PinnedTimelineSubject;
use App\Innoclapps\Criteria\WithPinnedTimelineSubjectsCriteria;

class TimelineController extends ApiController
{
    /**
     * Get the resource changelog
     *
     * @param \Illuminate\Http\Request $request
     * @param int $recordId
     * @param string $resourceName
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $resourceName, $recordId)
    {
        $request->validate(['resources' => 'sometimes|array']);

        $resource     = Innoclapps::resourceByName($resourceName);
        $record       = $resource->repository()->find($recordId);
        $hasChangelog = $record->isRelation('changelog');

        // When there is no resources included for the changelog and
        // the resource record does not have the changelog relation
        // in this case, 404 error will be shown
        if ($this->getResourcesForChangelog($request)->isEmpty()) {
            abort_unless($hasChangelog, 404);
        }

        $this->authorize('view', $record);

        $changelog = collect([])->when($hasChangelog, function ($collection) use ($record, $request) {
            ChangelogResource::topLevelResource($record);

            return $this->resolveChangelogJsonResource($record, $request);
        })->when(true, function ($collection) use ($record, $request) {
            $this->resolveResourcesJsonResource($record, $request)
                ->each(function ($data) use ($collection) {
                    $collection->push(...$data);
                });

            return $collection;
        })->sortBy([['is_pinned', 'desc'], ['pinned_date', 'desc'], ['created_at', 'desc']]);

        return $this->response(['data' => $changelog->values()->all()]);
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param \App\Innoclapps\Models\Model $record
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function resolveChangelogJsonResource($record, $request)
    {
        $query = $record->changelog()
            ->select(Resource::prefixColumns($record->changelog()->getModel()))
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc')
            ->orderBy($record->changelog()->getModel()->getQualifiedCreatedAtColumn(), 'desc');

        return collect(ChangelogResource::collection(
            WithPinnedTimelineSubjectsCriteria::applyQuery($query, $record, $record->changelog()->getModel())->paginate($request->input('per_page', 15))
        )->resolve());
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param \App\Innoclapps\Models\Model $record
     * @param \Illuminate\Http\Request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function resolveResourcesJsonResource($record, $request)
    {
        return $this->getResourcesForChangelog($request)->map(function ($resource) use ($record, $request) {
            $resource->jsonResource()::topLevelResource($record);

            return $resource->createJsonResource($resource->timelineQuery($record)->paginate($request->input('per_page')), true);
        });
    }

    /**
     * Get the resources that should be added in the changelog
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getResourcesForChangelog($request)
    {
        return collect($request->input('resources', []))->map(function ($resourceName) {
            return Innoclapps::resourceByName($resourceName);
        })->reject(function ($resource) {
            return ! Timelineables::isTimelineable($resource->repository()->getModel());
        })->values();
    }
}
