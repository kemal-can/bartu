<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Note;
use App\Models\Contact;
use Tests\Concerns\TestsMentions;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Notification;
use App\Contracts\Repositories\NoteRepository;

class NoteRepositoryTest extends TestCase
{
    use TestsMentions;

    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(NoteRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_send_notifications_to_mentioned_users_when_note_is_created()
    {
        $user = $this->signIn();

        $mentionUser = $this->createUser();
        $contact     = Contact::factory()->create();

        $attributes = array_merge(Note::factory()->for($user)->make()->toArray(), [
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
            'body'            => 'Other Text - ' . $this->mentionText($mentionUser->id, $mentionUser->name),
        ]);

        Notification::fake();

        $note = $this->repository->create($attributes);

        Notification::assertSentTo($mentionUser, UserMentioned::class, function ($notification) use ($contact, $note) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=notes&resourceId={$note->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_note_is_updated()
    {
        $user = $this->signIn();

        $mentionUser = $this->createUser();
        $note        = Note::factory()->for($user)->create();
        $contact     = Contact::factory()->create();

        $attributes = [
            'body'            => $note->body . $this->mentionText($mentionUser->id, $mentionUser->name),
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
        ];

        Notification::fake();

        $this->repository->update($attributes, $note->id);

        Notification::assertSentTo($mentionUser, UserMentioned::class, function ($notification) use ($contact, $note) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=notes&resourceId={$note->id}";
        });
    }
}
