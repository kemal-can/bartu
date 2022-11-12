<?php

namespace Tests\Unit\Innoclapps\Repository;

use Tests\TestCase;
use App\Innoclapps\Facades\Fields;
use Tests\Concerns\TestsCustomFields;
use Illuminate\Support\Facades\Schema;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class CustomFieldRepositoryTest extends TestCase
{
    use TestsCustomFields;

    public function test_field_column_is_altered_when_custom_field_is_created()
    {
        $repository = app(CustomFieldRepository::class);

        foreach ($this->fieldsTypesThatRequiresDatabaseColumnCreation() as $type) {
            $field = $repository->create([
                'field_type'    => $type,
                'field_id'      => 'cf_some_id_' . strtolower($type),
                'label'         => $type,
                'resource_name' => 'contacts',
                'options'       => in_array($type, Fields::getNonOptionableFieldsTypes()) ? [] : ['option-1'],
            ]);

            $this->assertTrue(Schema::hasColumn('contacts', $field->field_id));
        }
    }

    public function test_field_column_is_dropped_when_custom_field_is_deleted()
    {
        $repository = resolve(CustomFieldRepository::class);

        foreach ($this->fieldsTypesThatRequiresDatabaseColumnCreation() as $type) {
            $field = $repository->create([
                'field_type'    => $type,
                'field_id'      => 'cf_some_id_' . strtolower($type),
                'label'         => $type,
                'resource_name' => 'contacts',
                'options'       => in_array($type, Fields::getNonOptionableFieldsTypes()) ? [] : ['option-1'],
            ]);

            $repository->delete($field->id);
            $this->assertFalse(Schema::hasColumn('contacts', $field->field_id));
        }
    }

    public function test_db_columns_are_not_created_when_multi_optionable_custom_field_is_added()
    {
        $this->signIn();
        $repository = resolve(CustomFieldRepository::class);

        foreach ($this->fieldsTypesThatDoesntRequiresDatabaseColumnCreation() as $type) {
            $field = $repository->create([
                'field_type'    => $type,
                'field_id'      => 'cf_some_id_' . strtolower($type),
                'label'         => $type,
                'resource_name' => 'contacts',
                'options'       => ['option-1'],
            ]);

            $this->assertFalse(Schema::hasColumn('contacts', $field->field_id));
        }
    }
}
