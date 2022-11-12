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

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Repositories\RoleRepository;

class RoleController extends ApiController
{
    /**
     * Initialize new RoleController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\RoleRepository $repository
     */
    public function __construct(protected RoleRepository $repository)
    {
    }

    /**
     * Display a listing of the roles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = $this->repository->with(['permissions'])->orderBy('name', 'asc')->all();

        return $this->response(
            RoleResource::collection($roles)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $role = $this->repository->with(['permissions'])->find($id);

        return $this->response(new RoleResource($role));
    }

    /**
     * Store a newly created role in storage
     *
     * @param \App\Http\Requests\RoleRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RoleRequest $request)
    {
        $role = $this->repository->create($request->all());

        return $this->response(
            new RoleResource($this->repository->with('permissions')->find($role->id)),
            201
        );
    }

    /**
     * Update the specified role in storage
     *
     * @param int $id
     * @param \App\Http\Requests\RoleRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, RoleRequest $request)
    {
        $role = $this->repository->update($request->all(), $id);

        return $this->response(
            new RoleResource($this->repository->with('permissions')->find($role->id))
        );
    }

    /**
     * Remove the specified role from storage
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
