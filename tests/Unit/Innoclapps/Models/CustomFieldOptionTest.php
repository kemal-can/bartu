<?php

namespace Tests\Unit\Innoclapps\Models;

use Tests\TestCase;
use App\Innoclapps\Models\CustomField;
use App\Innoclapps\Models\CustomFieldOption;

class CustomFieldOptionTest extends TestCase
{
    public function test_custom_field_option_has_field()
    {
        $field = $this->makeField();
        $field->save();

        $option = new CustomFieldOption(['name' => 'Option 1']);
        $field->options()->save($option);

        $this->assertInstanceof(CustomField::class, $option->field);
    }

    protected function makeField($attrs = [])
    {
        return new CustomField(array_merge([
            'field_id'      => 'field_id',
            'field_type'    => 'Text',
            'resource_name' => 'resource',
            'label'         => 'Label',
         ], $attrs));
    }
}
