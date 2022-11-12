<?php

namespace Database\Factories;

use App\Models\CallOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallOutcomeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CallOutcome::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'         => $this->faker->unique()->catchPhrase(),
            'swatch_color' => $this->faker->hexColor(),
        ];
    }
}
