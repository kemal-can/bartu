<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Billable;
use App\Models\BillableProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillableProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BillableProduct::class;

    /**
     * @var int
     */
    protected static $displayOrder = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'billable_id'   => Billable::factory()->withBillableable(),
            'name'          => $name = $this->faker->catchPhrase(),
            'description'   => $this->faker->paragraph(1),
            'unit_price'    => $this->faker->randomFloat(3, 300, 4500),
            'qty'           => 1,
            'tax_label'     => 'TAX',
            'tax_rate'      => 0,
            'display_order' => static::$displayOrder,
            'product_id'    => Product::factory()->state(function ($attributes) use ($name) {
                return ['name' => $name];
            }),
        ];
    }

    /**
    * Configure the model factory.
    *
    * @return self
    */
    public function configure()
    {
        return $this->afterCreating(function (BillableProduct $product) {
            static::$displayOrder = $product->display_order + 1;
        });
    }

    /**
     * Add that the product billable with be tax exclusive.
    */
    public function withTaxExclusiveBillable()
    {
        return $this->for(Billable::factory()->withBillableable()->taxExclusive());
    }

    /**
     * Add that the product billable with be tax inclusive.
    */
    public function withTaxInclusiveBillable()
    {
        return $this->for(Billable::factory()->withBillableable()->taxInclusive());
    }

    /**
     * Add that the product billable with be without tax.
    */
    public function withBillableWithoutTax()
    {
        return $this->for(Billable::factory()->withBillableable()->noTax());
    }
}
