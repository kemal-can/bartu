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

namespace App\Support\Concerns;

use App\Models\Billable;

trait HasProducts
{
    /**
     * Provide the total column to be updated whenever the billable is updated
     *
     * @return string
     */
    public function totalColumn() : ?string
    {
        return null;
    }

    /**
    * Get the deal billable model
    */
    public function billable()
    {
        return tap($this->morphOne(Billable::class, 'billableable'), function ($instance) {
            if ($type = Billable::defaultTaxType()) {
                $instance->withDefault([
                    'tax_type' => $type,
                ]);
            }
        });
    }

    /**
    * Get all of the products for the model.
    */
    public function products()
    {
        return $this->hasManyThrough(
            \App\Models\BillableProduct::class,
            \App\Models\Billable::class,
            'billableable_id',
            'billable_id',
            'id',
            'id'
        )->where('billableable_type', $this::class);
    }
}
