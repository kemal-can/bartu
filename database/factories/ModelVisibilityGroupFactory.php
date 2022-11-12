<?php

namespace Database\Factories;

use App\Models\ModelVisibilityGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelVisibilityGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModelVisibilityGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => 'all',
        ];
    }

    /**
     * Indicate that the visibility group is teams related.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function teams()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'teams',
            ];
        });
    }

    /**
     * Indicate that the visibility group is users related.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function users()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'users',
            ];
        });
    }
}
