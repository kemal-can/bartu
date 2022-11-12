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

namespace App\Resources\Actions;

use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Innoclapps\Criteria\WithTrashedCriteria;

class RestoreAction extends Action
{
    /**
     * Handle method
     *
     * @param \Illuminate\Support\Collection $models
     * @param \App\Innoclapps\Actions\ActionFields $fields
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        $models[0]::resource()->repository()->restore($models);
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->can('view', $model);
    }

    /**
     * Query the models for execution
     *
     * @param array $ids
     * @param \App\Innoclapps\Repository\AppRepository $repository
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findModelsForExecution($ids, $repository)
    {
        return $repository->pushCriteria(WithTrashedCriteria::class)->findMany($ids);
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('app.soft_deletes.restore');
    }
}
