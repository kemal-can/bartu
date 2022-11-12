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

namespace App\Resources\Activity\Actions;

use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Contracts\Repositories\ActivityRepository;

class MarkActivityAsComplete extends Action
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
        $repository = resolve(ActivityRepository::class);

        foreach ($models as $model) {
            $repository->complete($model->id);
        }
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->can('changeState', $model);
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('activity.mark_as_completed');
    }
}
