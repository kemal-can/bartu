<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\User;
use App\Models\CallOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Call::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'body'            => $this->faker->paragraph(),
            'date'            => $this->faker->dateTimeBetween('-6 months')->format('Y-m-d H:i') . ':00',
            'call_outcome_id' => CallOutcome::factory(),
            'user_id'         => User::factory(),
        ];
    }
}
