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
use App\Http\Resources\StageResource;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\StageRepository;
use App\Contracts\Repositories\PipelineRepository;

class PipelineStageController extends ApiController
{
    /**
      * Retrieve pipeline stages
      *
      * @param int $id
      * @param \App\Contracts\Repositories\StageRepository $repository
      * @param \App\Contracts\Repositories\PipelineRepository $pipelineRepository
      * @param \Illuminate\Http\Request $request
      *
      * @return \Illuminate\Http\JsonResponse
      */
    public function index($id, StageRepository $repository, PipelineRepository $pipelineRepository, Request $request)
    {
        $this->authorize('view', $pipelineRepository->find($id));

        return $this->response(
            StageResource::collection($repository->scopeQuery(function ($query) use ($id) {
                return $query->where('pipeline_id', $id);
            })->paginate($request->input('per_page')))
        );
    }
}
