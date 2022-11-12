<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class CustomFieldsSeeder extends Seeder
{
    /**
     * The resource name the custom fields are intended for
     *
     * @var string
     */
    public static $resourceName = 'contacts';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $repository = resolve(CustomFieldRepository::class);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_checkbox',
            'field_type'    => 'Checkbox',
            'label'         => 'Checkbox',
            'options'       => [['name' => 'Checkbox 1'], ['name' => 'Checkbox 2']],
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_boolean',
            'field_type'    => 'Boolean',
            'label'         => 'Boolean',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_date',
            'field_type'    => 'Date',
            'label'         => 'Date',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_datetime',
            'field_type'    => 'DateTime',
            'label'         => 'DateTime',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_work_email',
            'field_type'    => 'Email',
            'label'         => 'Work Email',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_multiselect',
            'field_type'    => 'MultiSelect',
            'label'         => 'MultiSelect',
            'options'       => [['name' => 'MultiSelect 1'], ['name' => 'MultiSelect 2']],
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_number',
            'field_type'    => 'Number',
            'label'         => 'Number',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_numeric',
            'field_type'    => 'Numeric',
            'label'         => 'Numeric',
        ]);


        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_radio',
            'field_type'    => 'Radio',
            'label'         => 'Radio',
            'options'       => [['name' => 'Radio 1'], ['name' => 'Radio 2']],
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_select',
            'field_type'    => 'Select',
            'label'         => 'Select',
            'options'       => [['name' => 'Select 1'], ['name' => 'Select 2']],
        ]);


        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_text',
            'field_type'    => 'Text',
            'label'         => 'Text',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_textarea',
            'field_type'    => 'Textarea',
            'label'         => 'Textarea',
        ]);

        $repository->create([
            'resource_name' => static::$resourceName,
            'field_id'      => 'cf_custom_field_timezone',
            'field_type'    => 'Timezone',
            'label'         => 'Timezone',
        ]);
    }
}
