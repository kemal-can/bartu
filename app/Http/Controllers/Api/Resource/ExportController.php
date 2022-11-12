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

use App\Criteria\ExportRequestCriteria;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Criteria\FilterRulesCriteria;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Resources\Http\ResourceRequest;

class ExportController extends ApiController
{
    /**
     * Export resource data
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function handle(ResourceRequest $request)
    {
        abort_unless($request->resource() instanceof Exportable, 404);

        $repository = $request->resource()->repository();
        $repository->pushCriteria(new ExportRequestCriteria($request));

        if ($ownCriteria = $request->resource()->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        if ($filters = $request->filters) {
            $repository->pushCriteria(
                new FilterRulesCriteria($filters, $request->resource()->filtersForResource($request), $request)
            );
        }

        return $request->resource()
            ->exportable($repository)
            ->download($request->type);
    }
}
