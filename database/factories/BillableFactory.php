<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Enums\TaxType;
use App\Models\Billable;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Billable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tax_type' => TaxType::random(),
        ];
    }

    /**
     * Indicates that the billable has billableable
     *
     * @param mixed $for
     *
     * @return self
     **/
    public function withBillableable($for = null)
    {
        return $this->for($for ?? Deal::factory(), 'billableable');
    }

    /**
     * Indicate billable will be tax exclusive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function taxExclusive()
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::exclusive,
            ];
        });
    }

    /**
     * Indicate billable will be tax inclusive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function taxInclusive()
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::inclusive,
            ];
        });
    }

    /**
     * Indicate billable will have no tax.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function noTax()
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => TaxType::no_tax,
            ];
        });
    }
}
