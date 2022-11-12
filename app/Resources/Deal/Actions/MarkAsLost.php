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

use App\Enums\DealStatus;
use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Contracts\Repositories\DealRepository;
use App\Resources\Deal\Fields\LostReasonField;
use App\Innoclapps\Resources\Http\ResourceRequest;

class MarkAsLost extends Action
{
    /**
     * Indicates that the action will be hidden on the view/update view
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
            $repository->changeStatus(DealStatus::lost, $model, $fields->lost_reason);
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
            LostReasonField::make('lost_reason', __('deal.lost_reasons.lost_reason'))->rules('nullable', 'string', 'max:191'),
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
        return __('deal.actions.mark_as_lost');
    }
}
