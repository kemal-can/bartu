<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Call;
use App\Models\Contact;
use Tests\Concerns\TestsMentions;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Notification;
use App\Contracts\Repositories\CallRepository;

class CallRepositoryTest extends TestCase
{
    use TestsMentions;

    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(CallRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_send_notifications_to_mentioned_users_when_call_is_created()
    {
        $user = $this->signIn();

        $mentionUser = $this->createUser();
        $contact     = Contact::factory()->create();

        $attributes = array_merge(Call::factory()->for($user)->make()->toArray(), [
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
            'body'            => 'Other Text - ' . $this->mentionText($mentionUser->id, $mentionUser->name),
        ]);

        Notification::fake();

        $call = $this->repository->create($attributes);

        Notification::assertSentTo($mentionUser, UserMentioned::class, function ($notification) use ($contact, $call) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=calls&resourceId={$call->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_call_is_updated()
    {
        $user = $this->signIn();

        $mentionUser = $this->createUser();
        $call        = Call::factory()->for($user)->create();
        $contact     = Contact::factory()->create();

        $attributes = [
            'call_outcome_id' => $call->call_outcome_id,
            'body'            => $call->body . $this->mentionText($mentionUser->id, $mentionUser->name),
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
        ];

        Notification::fake();

        $this->repository->update($attributes, $call->id);

        Notification::assertSentTo($mentionUser, UserMentioned::class, function ($notification) use ($contact, $call) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=calls&resourceId={$call->id}";
        });
    }
}
