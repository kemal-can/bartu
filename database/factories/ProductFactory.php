<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->catchPhrase(),
            'description' => $this->faker->paragraph(1),
            'unit_price'  => $this->faker->randomFloat(3, 300, 4500),
            'sku'         => strtoupper(Str::random(6)),
            'is_active'   => true,
            'tax_rate'    => array_rand(array_flip([0, 10, 18])),
            'tax_label'   => 'TAX',
            'created_by'  => User::factory(),
        ];
    }

    /**
       * Indicate that the product is active.
       *
       * @return \Illuminate\Database\Eloquent\Factories\Factory
       */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the product is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
