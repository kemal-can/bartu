<?php

namespace Database\Factories;

use App\Models\ModelVisibilityGroup;
use App\Models\ModelVisibilityGroupDependent;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelVisibilityGroupDependentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModelVisibilityGroupDependent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'model_visibility_group_id' => ModelVisibilityGroup::factory(),
        ];
    }
}
