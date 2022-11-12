<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WebForm;
use App\Models\Pipeline;
use Illuminate\Support\Str;
use App\Enums\WebFormSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebFormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WebForm::class;

    /**
     * Submit data to merge
     *
     * @var array
     */
    protected $mergeSubmitData = [];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $pipeline = Pipeline::factory()->withStages()->create();

        return [
            'title'   => 'Web Form',
            'status'  => 'active',
            'locale'  => 'en',
            'user_id' => User::factory(),
            'styles'  => [
                'primary_color'    => $this->faker->hexColor(),
                'background_color' => $this->faker->hexColor(),
            ],
            'notifications' => [$this->faker->safeEmail()],
            'submit_data'   => array_merge([
                'pipeline_id'   => $pipeline->id,
                'stage_id'      => $pipeline->stages()->first()->id,
                'action'        => 'message',
                'success_title' => 'Form submitted.',
            ], $this->mergeSubmitData),
            'sections'   => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the web form has introduction section.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withIntroductionSection($merge = [])
    {
        return $this->state(function (array $attributes) use ($merge) {
            $attributes['sections'][] = array_merge([
                'title'   => 'Introduction Title',
                'message' => 'Introduction Message',
            ], $merge, ['type' => WebFormSection::INTRODUCTION]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Add new field section to the web form
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function addFieldSection($fieldAttribute, $resource = 'contacts', $merge = [])
    {
        return $this->state(function (array $attributes) use ($merge, $fieldAttribute, $resource) {
            $attributes['sections'][] = array_merge([
                'label'            => 'Field ' . $fieldAttribute . ' Label',
                'attribute'        => $fieldAttribute,
                'isRequired'       => false,
                'requestAttribute' => Str::random(),
                'resourceName'     => $resource,
            ], $merge, ['type' => WebFormSection::FIELD]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
      * Add new file section to the web form.
      *
      * @return \Illuminate\Database\Eloquent\Factories\Factory
      */
    public function addFileSection($resource = 'contacts', $merge = [])
    {
        return $this->state(function (array $attributes) use ($merge, $resource) {
            $attributes['sections'][] = array_merge([
                'label'            => 'Attachment',
                'isRequired'       => false,
                'multiple'         => false,
                'requestAttribute' => Str::random(),
                'resourceName'     => $resource,
            ], $merge, ['type' => WebFormSection::FILE]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Indicate that the web form has submit button section.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withSubmitButtonSection($merge = [])
    {
        return $this->state(function (array $attributes) use ($merge) {
            $attributes['sections'][] = array_merge(['text' => 'Submit'], $merge, ['type' => WebFormSection::SUBMIT]);

            return [
                'sections' => $attributes['sections'],
            ];
        });
    }

    /**
     * Merge the given submit data
     *
     * @param array $data
     * @return self
     */
    public function mergeSubmitData($data)
    {
        $this->mergeSubmitData = $data;

        return $this;
    }

    /**
     * Indicate that the web form is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }
}
