<?php

namespace Database\Factories;

use App\Models\Phone;
use App\Enums\PhoneType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Phone::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => Phone::generateRandomPhoneNumber(),
            'type'   => PhoneType::cases()[array_rand(PhoneType::cases())],
        ];
    }
}
