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

use App\Http\Resources\TableResource;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ResourceTableRequest;
use App\Innoclapps\QueryBuilder\Exceptions\QueryBuilderException;

class TableController extends ApiController
{
    /**
     * Display a table listing of the resource
     *
     * @param \App\Http\Requests\ResourceTableRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ResourceTableRequest $request)
    {
        try {
            return $this->response(
                TableResource::collection($request->boolean('trashed') ?
            $request->resolveTrashedTable()->make() :
            $request->resolveTable()->make())
            );
        } catch (QueryBuilderException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get the resource table settings
     *
     * @param \App\Http\Requests\ResourceTableRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings(ResourceTableRequest $request)
    {
        return $this->response(
            $request->boolean('trashed') ?
            $request->resolveTrashedTable()->settings() :
            $request->resolveTable()->settings()
        );
    }

    /**
     * Customize the resource table
     *
     * @param \App\Http\Requests\ResourceTableRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customize(ResourceTableRequest $request)
    {
        $table = tap($request->resolveTable(), function ($table) {
            abort_unless($table->customizeable, 403, 'This table cannot be customized.');
        });

        return $this->response(
            $table->settings()->update($request->all())
        );
    }
}
