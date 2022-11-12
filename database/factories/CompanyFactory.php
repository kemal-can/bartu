<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->company(),
            'email'       => $this->faker->unique()->safeEmail(),
            'domain'      => $this->faker->domainName(),
            'street'      => $this->faker->streetAddress(),
            'city'        => $this->faker->city(),
            'state'       => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'created_at'  => $this->faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
            'created_by'  => User::factory(),
        ];
    }
}
