<?php

namespace Tests\Feature\Resource\Note;

use App\Models\Contact;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\ActivityTypeSeeder;
use Tests\Feature\Resource\ResourceTestCase;

class NoteResourceTest extends ResourceTestCase
{
    protected $resourceName = 'notes';

    public function test_user_can_create_resource_record()
    {
        $this->signIn();

        $contact = Contact::factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body'            => 'Note Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
            'contacts'        => [$contact->id],
        ])->assertCreated()->assertJson([
            'body' => 'Note Body',
        ])->assertJsonCount(1, 'contacts');
    }

    public function test_user_can_create_resource_record_with_associations_attribute()
    {
        $this->signIn();

        $contact = Contact::factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body'            => 'Note Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
            'associations'    => [
                'contacts' => [$contact->id],
            ],
        ])->assertCreated()->assertJsonCount(1, 'contacts');
    }

    public function test_user_can_update_resource_record()
    {
        $this->signIn();
        $note    = $this->factory()->create();
        $contact = Contact::factory()->create();

        $this->putJson($this->updateEndpoint($note), [
            'body'            => 'Updated Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
        ])->assertOk()->assertJson([
            'body' => 'Updated Body',
        ]);
    }

    public function test_user_can_update_only_own_created_note()
    {
        $user    = $this->asRegularUser()->createUser();
        $contact = Contact::factory()->create();
        $this->signIn($user);
        $user2 = $this->createUser();
        $note  = $this->factory()->for($user2)->create();

        $this->putJson($this->updateEndpoint($note), [
            'body'            => 'Updated Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
        ])->assertForbidden();
    }

    public function test_note_requires_body()
    {
        $this->signIn();
        $note = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body' => '',
        ])->assertJsonValidationErrorFor('body');

        $this->putJson($this->updateEndpoint($note), [
            'body' => '',
        ])->assertJsonValidationErrorFor('body');
    }

    public function test_note_requires_via_resource()
    {
        $this->signIn();
        $note    = $this->factory()->create();
        $contact = Contact::factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body'            => 'Note Body',
            'via_resource_id' => $contact->id,
            'via_resource'    => '',
        ])->assertJsonValidationErrorFor('via_resource');
        $this->putJson($this->updateEndpoint($note), [
            'body'            => 'Note Body',
            'via_resource_id' => $contact->id,
            'via_resource'    => '',
        ])->assertJsonValidationErrorFor('via_resource');
    }

    public function test_note_requires_via_resource_id()
    {
        $this->signIn();
        $note = $this->factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body'            => 'Note Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => '',
        ])->assertJsonValidationErrorFor('via_resource_id');

        $this->putJson($this->updateEndpoint($note), [
            'body'            => 'Note Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => '',
        ])->assertJsonValidationErrorFor('via_resource_id');
    }

    public function test_user_can_retrieve_resource_records()
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(5, 'data');
    }

    public function test_user_can_retrieve_notes_that_are_associated_with_related_records_the_user_is_authorized_to_see()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->createUser();
        $this->signIn($user);
        $user2 = $this->createUser();
        $this->factory()->create();
        $this->factory()->for($user2)->create();
        $this->factory()->for($user)->has(Contact::factory()->for($user))->create();

        $this->getJson($this->indexEndpoint())->assertJsonCount(1, 'data');
    }

    public function test_user_can_retrieve_resource_record()
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->getJson($this->showEndpoint($record))->assertOk();
    }

    public function test_user_can_retrieve_only_own_created_note()
    {
        $user = $this->asRegularUser()->createUser();
        $this->signIn($user);
        $user2 = $this->createUser();
        $note  = $this->factory()->for($user2)->create();

        $this->getJson($this->showEndpoint($note))->assertForbidden();
    }

    public function test_user_can_delete_resource_record()
    {
        $this->signIn();

        $record = $this->factory()->create();

        $this->deleteJson($this->deleteEndpoint($record))->assertNoContent();
    }

    public function test_user_can_delete_only_own_created_note()
    {
        $user = $this->asRegularUser()->createUser();
        $this->signIn($user);
        $user2 = $this->createUser();
        $note  = $this->factory()->for($user2)->create();

        $this->deleteJson($this->deleteEndpoint($note))->assertForbidden();
    }

    public function test_user_can_create_note_and_follow_up_task()
    {
        $this->withUserAttrs(['timezone' => 'UTC'])->signIn();
        $this->seed(ActivityTypeSeeder::class);
        $contact = Contact::factory()->create();

        $this->postJson($this->createEndpoint(), [
            'body'            => 'Note Body',
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
            'contacts'        => [$contact->id],
            'task_date'       => $date = date('Y-m-d'),
        ])->assertCreated()->assertJson([
            'createdActivity' => [
                'due_date' => [
                    'date' => $date,
                    'time' => value(function () {
                        return now()->setHour(config('app.defaults.hour'))
                            ->setMinute(config('app.defaults.minute'))
                            ->format('H:i');
                    }),
                ],
            ], ]);


        $this->assertCount(1, $contact->activities);
        $this->assertDatabaseHas('activities', [
            'note' => __('note.follow_up_task_body', [
                'content' => 'Note Body',
            ]),
        ]);
    }
}
