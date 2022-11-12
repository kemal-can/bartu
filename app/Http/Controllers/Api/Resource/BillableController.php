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

use App\Enums\TaxType;
use App\Models\Billable;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ApiController;
use App\Http\Resources\BillableResource;
use App\Innoclapps\Rules\NumericFieldCheckRule;
use App\Contracts\Repositories\BillableRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Contracts\BillableResource as BillableResourceContract;

class BillableController extends ApiController
{
    /**
     * Handle the resource billable request
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param \App\Contracts\Repositories\BillableRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(ResourceRequest $request, BillableRepository $repository)
    {
        abort_unless($request->resource() instanceof BillableResourceContract, 404);

        $this->authorize('update', $request->record());

        $request->validate([
            'tax_type'                 => ['nullable', 'string', Rule::in(TaxType::names())],
            'description'              => 'nullable|string', // todo, is it used?
            'products.*.name'          => 'sometimes|required|string|max:191',
            'products.*.discount_type' => 'nullable|string|in:fixed,percent',
            'products.*.display_order' => 'integer',
            'products.*.qty'           => 'nullable|regex:/^[0-9]\d*(\.\d{0,2})?$/',
            'products.*.unit'          => 'nullable|max:191',
            'products.*.tax_label'     => 'nullable|string|max:191',
            'products.*.tax_rate'      => ['nullable', new NumericFieldCheckRule],
            'products.*.product_id'    => 'nullable|integer',
        ]);

        $billable = with($repository->save($request->all(), $request->record()), function ($instance) use ($repository) {
            $billable = $repository->with('products')->find($instance->getKey());
            $billable->wasRecentlyCreated = $instance->wasRecentlyCreated;

            return $billable;
        });

        return $this->response(new BillableResource($billable));
    }
}
