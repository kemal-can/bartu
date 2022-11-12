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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\DashboardResource;
use App\Innoclapps\Contracts\Repositories\DashboardRepository;

class DashboardController extends ApiController
{
    /**
     * Initialize new DashboardController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\DashboardRepository $repository
     */
    public function __construct(protected DashboardRepository $repository)
    {
    }

    /**
     * Display a listing of the current user dashboards
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $dashboards = $this->repository->forUser($request->user());

        return $this->response(
            DashboardResource::collection($dashboards)
        );
    }

    /**
     * Display the specified dashboard.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $dashboard = $this->repository->find($id);

        $this->authorize('view', $dashboard);

        return $this->response(new DashboardResource($dashboard));
    }

    /**
     * Store a newly created dashboard in storage
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:191',
            'cards.*.key' => 'sometimes|required',
        ]);

        $dashboard = $this->repository->createForUser($request->all(), $request->user());

        return $this->response(new DashboardResource($dashboard), 201);
    }

    /**
     * Update the specified dashboard in storage
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'name'        => 'sometimes|required|string|max:191',
            'cards.*.key' => 'sometimes|required',
        ]);

        $this->authorize('update', $this->repository->find($id));
        $dashboard = $this->repository->update($request->only(['name', 'is_default', 'cards']), $id);

        return $this->response(new DashboardResource($dashboard));
    }

    /**
     * Remove the specified dashboard from storage
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', $dashboard = $this->repository->find($id));

        if ($this->repository->hasOnlyOneDashboard($dashboard->user_id)) {
            abort(409, 'There must be at least one active dashboard.');
        }

        $this->repository->delete($id);

        return $this->response('', 204);
    }
}
