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

namespace App\Http\Resources;

use App\Innoclapps\JsonResource;

class BillableResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'tax_type'       => $this->tax_type->name,
            'sub_total'      => $this->sub_total,
            'has_discount'   => $this->has_discount,
            'total_discount' => $this->total_discount,
            'total_tax'      => $this->total_tax,
            'applied_taxes'  => $this->getAppliedTaxes(),
            'total'          => $this->total,
            // 'terms'    => $this->terms,
            // 'notes'    => $this->notes,
            'products' => $this->relationLoaded('products') ?
                BillableProductResource::collection($this->products) :
                [],
        ], $request);
    }
}
