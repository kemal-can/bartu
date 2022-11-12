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

class BillableProductResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'product_id'     => $this->product_id,
            'name'           => $this->name,
            'description'    => $this->description,
            'unit_price'     => (float) $this->unit_price,
            'qty'            => (float) $this->qty,
            'unit'           => $this->unit,
            'tax_rate'       => (float) $this->tax_rate,
            'tax_label'      => $this->tax_label,
            'discount_type'  => $this->discount_type,
            'discount_total' => (float) $this->discount_total,
            'sku'            => $this->sku,
            'amount'         => $this->totalAmountWithDiscount(),
            'note'           => $this->note,
            'display_order'  => $this->display_order,
        ], $request);
    }
}
