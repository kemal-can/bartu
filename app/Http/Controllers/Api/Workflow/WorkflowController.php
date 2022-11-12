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

namespace App\Http\Controllers\Api\Workflow;

use App\Http\Requests\WorkflowRequest;
use App\Http\Controllers\ApiController;
use App\Http\Resources\WorkflowResource;
use App\Innoclapps\Contracts\Repositories\WorkflowRepository;

class WorkflowController extends ApiController
{
    /**
     * Initialize new WorkflowController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\WorkflowRepository $repository
     */
    public function __construct(protected WorkflowRepository $repository)
    {
    }

    /**
     * Get the stored workflows
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->response(
            WorkflowResource::collection($this->repository->all())
        );
    }

    /**
     * Display the specified workflow.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->response(new WorkflowResource(
            $this->repository->find($id)
        ));
    }

    /**
     * Store a newly created workflow in storage
     *
     * @param \App\Http\Requests\WorkflowRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(WorkflowRequest $request)
    {
        $workflow = $this->repository->create($request->createData());

        return $this->response(new WorkflowResource($workflow), 201);
    }

    /**
     * Update the specified workflow in storage
     *
     * @param int $id
     * @param \App\Http\Requests\WorkflowRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, WorkflowRequest $request)
    {
        $workflow = $this->repository->update($request->createData(), $id);

        return $this->response(new WorkflowResource($workflow));
    }

    /**
     * Remove the specified workflow from storage
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
