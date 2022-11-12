<?php

namespace Database\Factories;

use App\Models\ActivityType;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Calendar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email'            => $this->faker->safeEmail(),
            'user_id'          => User::factory(),
            'activity_type_id' => ActivityType::factory(),
            'activity_types'   => [ActivityType::factory()->create(), ActivityType::factory()->create()],
        ];
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
