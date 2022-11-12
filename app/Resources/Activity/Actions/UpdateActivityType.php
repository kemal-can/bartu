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
use App\Resources\Activity\Fields\ActivityType;
use App\Contracts\Repositories\ActivityRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class UpdateActivityType extends Action
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
            $repository->update(['activity_type_id' => $fields->activity_type_id], $model->id);
        }
    }

    /**
     * Get the action fields
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function fields(ResourceRequest $request) : array
    {
        return [
            ActivityType::make()->rules('required'),
        ];
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->can('update', $model);
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('activity.actions.update_type');
    }
}
