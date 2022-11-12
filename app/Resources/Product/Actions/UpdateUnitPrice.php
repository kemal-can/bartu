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

namespace App\Resources\Product\Actions;

use App\Innoclapps\Actions\Action;
use App\Innoclapps\Fields\Numeric;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Contracts\Repositories\ProductRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class UpdateUnitPrice extends Action
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
        $repository = resolve(ProductRepository::class);

        foreach ($models as $model) {
            $repository->update(['unit_price' => $fields->unit_price], $model->getKey());
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
            Numeric::make('unit_price')
                ->rules('required')
                ->currency(),
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
        return __('product.actions.update_unit_price');
    }
}
