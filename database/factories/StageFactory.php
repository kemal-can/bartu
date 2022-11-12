<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Stage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'            => $this->faker->unique()->catchPhrase(),
            'win_probability' => 50,
            'pipeline_id'     => Pipeline::factory(),
        ];
    }
}
