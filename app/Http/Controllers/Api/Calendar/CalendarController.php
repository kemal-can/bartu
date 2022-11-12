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

namespace App\Http\Controllers\Api\Calendar;

use Illuminate\Http\Request;
use App\Contracts\Calendarable;
use App\Innoclapps\Facades\Innoclapps;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CalendarEventResource;

class CalendarController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $requst
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $events = collect([]);

        foreach ($this->filterResourcesForCalendar($request) as $resource) {
            $repository = $resource->repository();

            $events = $events->merge($repository->scopeQuery(
                fn ($query) => $this->applyQuery($query, $repository, $resource, $request)
            )->all());
        }

        return $this->response(
            CalendarEventResource::collection($events)
        );
    }

    /**
     * Apply the calendar query
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     * @param \App\Innoclapps\Resources\Resource $resource
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyQuery($query, $repository, $resource, $request)
    {
        $startColumn = $resource->model()::getCalendarStartColumnName();
        $endColumn   = $resource->model()::getCalendarEndColumnName();

        $query = $query->whereNotNull($startColumn)
            ->whereNotNull($endColumn)
            ->where(function ($query) use ($startColumn, $endColumn, $request, $repository) {
                $query->where(function ($query) use ($startColumn, $endColumn, $request) {
                    // https://stackoverflow.com/questions/17014066/mysql-query-to-select-events-between-start-end-date
                    $spanRaw = '"' . $request->start_date . '" between ' . $startColumn . ' AND ' . $endColumn;

                    return $query->whereBetween($startColumn, [
                        $request->start_date,
                        $request->end_date,
                    ])->orWhereRaw($spanRaw);
                });

                if (method_exists($repository, 'tapCalendarDateQuery')) {
                    $repository->tapCalendarDateQuery($query, $startColumn, $endColumn, $request);
                }
            });

        if (method_exists($repository, 'tapCalendarQuery')) {
            $repository->tapCalendarQuery($query, $request);
        }

        return $query;
    }

    /**
     * Filter the calendarable resources
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function filterResourcesForCalendar($request)
    {
        return Innoclapps::registeredResources()->filter(function ($resource) use ($request) {
            if (is_subclass_of($resource->model(), Calendarable::class)) {
                return $request->resource_name ? $resource->name() === $request->resource_name : true;
            }
        });
    }
}
