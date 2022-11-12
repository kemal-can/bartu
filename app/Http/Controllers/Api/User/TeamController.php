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

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\TeamRequest;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Auth;
use App\Criteria\Team\OwnTeamsCriteria;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\TeamRepository;

class TeamController extends ApiController
{
    /**
     * Create new TeamController instance
     *
     * @param \App\Contracts\Repositories\TeamRepository $repository
     */
    public function __construct(protected TeamRepository $repository)
    {
    }

    /**
     * Retrieve all teams
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $teams = $this->repository->with('users')
            ->pushCriteria(OwnTeamsCriteria::class)
            ->orderBy('name')
            ->all();

        return $this->response(
            TeamResource::collection($teams)
        );
    }

    /**
     * Create new team
     *
     * @param \App\Http\Requests\TeamRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TeamRequest $request)
    {
        $team = $this->repository->create($request->input());

        return $this->response(
            new TeamResource($team),
            201
        );
    }

    /**
     * Retrieve a team
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $team = $this->repository->with('users')->find($id);
        $user = Auth::user();

        abort_if(! $user->isSuperAdmin() && ! $user->belongsToTeam($team), 403);

        return $this->response(
            new TeamResource($team)
        );
    }

    /**
     * Update a team
     *
     * @param int $id
     * @param \App\Http\Requests\TeamRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, TeamRequest $request)
    {
        $team = $this->repository->update($request->input(), $id);

        return $this->response(
            new TeamResource($team),
        );
    }

    /**
     * Delete a team
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->repository->delete($id);

        return $this->response('', 204);
    }
}
