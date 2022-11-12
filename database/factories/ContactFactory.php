<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name'  => $this->faker->firstName(),
            'last_name'   => $this->faker->lastName(),
            'email'       => $this->faker->safeEmail(),
            'job_title'   => $this->faker->jobTitle(),
            'street'      => $this->faker->streetAddress(),
            'city'        => $this->faker->city(),
            'state'       => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'avatar'      => null,
            'created_at'  => $this->faker->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
            'created_by'  => User::factory(),
        ];
    }
}
