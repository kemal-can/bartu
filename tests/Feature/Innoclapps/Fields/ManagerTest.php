<?php

namespace Tests\Feature\Innoclapps\Fields;

use Tests\TestCase;
use App\Models\Contact;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Facades\Fields;
use Tests\Fixtures\SampleTableColumn;
use Illuminate\Support\Facades\Notification;
use Tests\Fixtures\SampleDatabaseNotification;
use App\Contracts\Repositories\ContactRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class ManagerTest extends TestCase
{
    public function test_customized_creation_fields_are_properly_sorted()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'Label'),
                Text::make('test_field_2', 'Label'),
            ];
        });

        Fields::customize([
            'test_field_1' => ['order' => 2],
            'test_field_2' => ['order' => 1],
        ], 'testing', Fields::UPDATE_VIEW);

        $fields = Fields::resolve('testing', Fields::UPDATE_VIEW);

        $this->assertEquals($fields[0]->attribute, 'test_field_2');
        $this->assertEquals($fields[1]->attribute, 'test_field_1');
    }

    public function test_customized_update_fields_are_properly_sorted()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'Label'),
                Text::make('test_field_2', 'Label'),
            ];
        });

        Fields::customize([
            'test_field_1' => ['order' => 2],
            'test_field_2' => ['order' => 1],
        ], 'testing', Fields::CREATE_VIEW);

        $fields = Fields::resolve('testing', Fields::CREATE_VIEW);

        $this->assertEquals($fields[0]->attribute, 'test_field_2');
        $this->assertEquals($fields[1]->attribute, 'test_field_1');
    }

    public function test_it_ensures_that_user_cannot_modify_the_primary_fields_attributes_on_creation_view()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'Label')->primary(),
            ];
        });

        Fields::customize([
            'test_field_1' => ['collapsed' => true, 'showOnCreation' => false],
        ], 'testing', Fields::CREATE_VIEW);

        $fields = Fields::resolveCreateFields('testing');

        $this->assertFalse($fields->first()->collapsed);
        $this->assertTrue($fields->first()->showOnCreation);
    }

    public function test_it_ensures_that_user_cannot_modify_the_primary_fields_attributes_on_update_view()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'Label')->primary(),
            ];
        });

        $notAllowedAttributes = array_diff(
            Fields::allowedCustomizableAttributes(),
            Fields::allowedCustomizableAttributesForPrimary()
        );

        Fields::customize([
            [
                'test_field_1' => collect($notAllowedAttributes)->mapWithKeys(function ($attribute) {
                    return [$attribute => '--some-value--'];
                })->all(),
            ],
        ], 'testing', Fields::UPDATE_VIEW);

        $fields = Fields::resolveUpdateFields('testing');

        foreach ($notAllowedAttributes as $attribute) {
            if ($attribute === 'isRequired') {
                $this->assertFalse($fields->first()->isRequired(resolve(ResourceRequest::class)));
            } else {
                $this->assertNotEquals('--some-value--', $fields->first()->{$attribute});
            }
        }
    }

    public function test_it_ensures_that_fields_excluded_from_settings_are_not_included()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'Label')->excludeFromSettings(),
                Text::make('test_field_2', 'Label'),
            ];
        });

        $fields = Fields::resolveForSettings('testing', Fields::UPDATE_VIEW);
        $this->assertCount(1, $fields);

        $fields = Fields::resolveForSettings('testing', Fields::CREATE_VIEW);
        $this->assertCount(1, $fields);
    }

    public function test_field_can_be_read_only()
    {
        $field = Text::make('test')->readOnly(true);

        $this->assertTrue(
            $field->isReadOnly(resolve(ResourceRequest::class))
        );

        $field->readOnly(function () {
            return false;
        });

        $this->assertFalse(
            $field->isReadOnly(resolve(ResourceRequest::class))
        );
    }

    public function test_field_can_have_custom_value_resolver()
    {
        $field = Text::make('test')->resolveUsing(function ($model) {
            return 'custom-value';
        });

        $contact = Contact::factory()->create();

        $this->assertEquals($field->resolve($contact), 'custom-value');
    }

    public function test_field_can_have_custom_import_resolver()
    {
        $field = Text::make('test')->importUsing(function ($value, $row, $original, $field) {
            return [$field->attribute => 'custom-value'];
        });

        $this->assertEquals(
            $field->resolveForImport('original-value', [], []),
            [$field->attribute => 'custom-value']
        );
    }

    public function test_field_index_column_can_be_swapped()
    {
        $field = Text::make('test')->swapIndexColumn(function ($value) {
            return new SampleTableColumn;
        });

        $this->assertInstanceOf(SampleTableColumn::class, $field->resolveIndexColumn());
    }

    public function test_field_index_column_can_be_tapped()
    {
        $field = Text::make('test')->swapIndexColumn(function ($value) {
            return (new SampleTableColumn)->primary(true);
        })->tapIndexColumn(function ($column) {
            $column->primary(false);
        });

        $this->assertFalse($field->resolveIndexColumn()->isPrimary());
    }

    public function test_it_makes_sure_changed_notification_is_successfully_triggered_for_user_field()
    {
        Notification::fake();

        $user = $this->signIn();

        Fields::replace('contacts', [
            Text::make('title'),
            User::make('User')
                ->notification(SampleDatabaseNotification::class),
        ]);

        $event   = Contact::factory()->for($user)->create();
        $newUser = $this->createUser();

        resolve(ContactRepository::class)->update([
            'user_id' => $newUser->id,
        ], $event->id);

        Notification::assertSentTo($newUser, SampleDatabaseNotification::class);
    }

    public function test_customized_fields_attributes_are_merged_when_fields_are_customized()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test'),
                Text::make('test_field_2', 'test'),
            ];
        });

        $fields     = [];
        $attributes = function () {
            return collect(Fields::allowedCustomizableAttributes())->mapWithKeys(function ($attribute) {
                if ($attribute === 'order') {
                    return [$attribute => 55];
                }

                if ($attribute === 'showOnCreation') {
                    return [$attribute => false];
                }

                if ($attribute === 'showOnUpdate') {
                    return [$attribute => false];
                }

                if ($attribute === 'showOnDetail') {
                    return [$attribute => false];
                }

                if ($attribute === 'collapsed') {
                    return [$attribute => true];
                }

                if ($attribute === 'isRequired') {
                    return [$attribute => true];
                }

                $this->markTestIncomplete('Attributes are missing.');
            })->all();
        };

        // Sets the showOnUpdate and showOnCreate to false because by default they are to true
        Fields::customize(
            $attributesUpdate = [
                'test_field_1' => $attributes(),
                'test_field_2' => $attributes(),
            ],
            'testing',
            Fields::UPDATE_VIEW
        );

        $fields[Fields::UPDATE_VIEW] = Fields::inGroup('testing', Fields::UPDATE_VIEW);

        Fields::customize(
            $attributesCreate = [
                'test_field_1' => $attributes(),
                'test_field_2' => $attributes(),
            ],
            'testing',
            Fields::CREATE_VIEW
        );

        $fields[Fields::CREATE_VIEW] = Fields::inGroup('testing', Fields::CREATE_VIEW);

        foreach ([Fields::UPDATE_VIEW, Fields::CREATE_VIEW] as $view) {
            foreach (['test_field_1', 'test_field_2'] as $field) {
                foreach (Fields::allowedCustomizableAttributes() as $attribute) {
                    if ($attribute != 'isRequired') {
                        $this->assertEquals(
                            ${'attributes' . ucfirst($view)}[$field][$attribute],
                            $fields[$view]->firstWhere('attribute', $field)->{$attribute}
                        );
                    } else {
                        $this->assertEquals(
                            ${'attributes' . ucfirst($view)}[$field][$attribute],
                            $fields[$view]->firstWhere('attribute', $field)->isRequired(app(ResourceRequest::class))
                        );
                    }
                }
            }
        }
    }
}
