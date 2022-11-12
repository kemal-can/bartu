<?php

namespace Tests\Feature\Controller\Api\Resource;

use Tests\TestCase;
use App\Models\Contact;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\Email;
use App\Innoclapps\Facades\Fields;

class FieldControllerTest extends TestCase
{
    public function test_resource_create_fields_can_be_retrieved()
    {
        $this->signIn();

        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name'),
            Email::make('make')->hideWhenCreating(),
        ]);

        $this->getJson('/api/contacts/create-fields')->assertJsonCount(2);
    }

    public function test_resource_update_fields_can_be_retrieved()
    {
        $this->signIn();
        $contact = Contact::factory()->create();
        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name')->hideWhenUpdating(),
        ]);

        $this->getJson('/api/contacts/' . $contact->id . '/update-fields')->assertJsonCount(1);
    }
}
