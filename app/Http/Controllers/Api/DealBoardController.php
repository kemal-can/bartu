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
use App\Support\DealBoard\Board;
use App\Criteria\Deal\OwnDealsCriteria;
use App\Http\Controllers\ApiController;
use App\Http\Resources\PipelineResource;
use App\Http\Resources\DealBoardResource;
use App\Innoclapps\Criteria\RequestCriteria;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\PipelineRepository;

class DealBoardController extends ApiController
{
    /**
     * Initialize new DealBoardController instance.
     *
     * @param \App\Contracts\Repositories\DealRepository $repository
     * @param \App\Contracts\Repositories\PipelineRepository $repository
     */
    public function __construct(protected DealRepository $repository, protected PipelineRepository $pipelineRepository)
    {
    }

    /**
     * Get the deals board
     *
     * @param int $pipelineId The pipeline id the board is intended for
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function board($pipelineId, Request $request)
    {
        $this->authorize('view', $this->pipelineRepository->find($pipelineId));

        $this->repository->pushCriteria(OwnDealsCriteria::class)
            ->pushCriteria(RequestCriteria::class);

        return $this->response(DealBoardResource::collection(
            (new Board($this->repository, $request))->data((int) $pipelineId)
        ));
    }

    /**
     * Update board card order and stage
     *
     * @param int $pipelineId
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function update($pipelineId, Request $request)
    {
        $this->authorize('view', $this->pipelineRepository->find($pipelineId));

        $request->validate([
            // Must be present for adding/removing the color
            '*.swatch_color' => 'present|max:7',
            '*.id'           => 'required',
            '*.stage_id'     => 'required',
            '*.board_order'  => 'required',
        ]);

        (new Board($this->repository, $request))->update($request->input());
    }

    /**
     * Get the deals board summary for the given pipeline
     *
     * @param int $pipelineId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary($pipelineId, Request $request)
    {
        $this->authorize('view', $this->pipelineRepository->find($pipelineId));

        $this->repository->pushCriteria(OwnDealsCriteria::class)
            ->pushCriteria(RequestCriteria::class);

        return $this->response((new Board($this->repository, $request))->summary((int) $pipelineId));
    }

    /**
     * Save board pipeline default sorting
     *
     * @param int $pipelineId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSort($pipelineId, Request $request)
    {
        $this->authorize('view', $this->pipelineRepository->find($pipelineId));

        $pipeline = $this->pipelineRepository->updateBoardDefaultSort(
            (int) $pipelineId,
            $request->user()->id,
            $request->all()
        );

        return $this->response(new PipelineResource(
            $this->pipelineRepository->withResponseRelations()->find($pipeline->id)
        ));
    }
}
