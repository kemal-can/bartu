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
use App\Contracts\Repositories\PipelineRepository;

class PipelineDisplayOrder extends ApiController
{
    /**
     * Save the pipelines orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Save the pipelines display order
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\PipelineRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, PipelineRepository $repository)
    {
        $request->validate([
            'order.*.id'            => 'required|int',
            'order.*.display_order' => 'required|int',
        ]);

        foreach ($request->input('order') as $pipeline) {
            $repository->saveDisplayOrder($pipeline['id'], $pipeline['display_order']);
        }

        return $this->response('', 204);
    }
}
