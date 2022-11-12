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

namespace App\Resources\Deal\Actions;

use App\Innoclapps\Fields\Select;
use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class ChangeDealStage extends Action
{
    /**
     * Indicates that the action will be hidden on the update view
     *
     * @var boolean
     */
    public bool $hideOnUpdate = true;

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
        $repository = resolve(DealRepository::class);

        foreach ($models as $model) {
            $repository->update(['stage_id' => $fields->stage_id], $model->id);
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
            Select::make('stage_id', __('fields.deals.stage.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->rules('required')
                ->options(function () use ($request) {
                    return resolve(StageRepository::class)->allStagesForOptions($request->user());
                }),
        ];
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return bool
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
        return __('deal.actions.change_stage');
    }
}
