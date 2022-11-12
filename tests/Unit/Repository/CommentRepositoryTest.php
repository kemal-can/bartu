<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Activity;
use Tests\Concerns\TestsMentions;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Notification;
use App\Contracts\Repositories\CommentRepository;

class CommentRepositoryTest extends TestCase
{
    use TestsMentions;

    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(CommentRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_send_notifications_to_mentioned_users_when_comment_is_created()
    {
        $this->signIn();

        $user     = $this->createUser();
        $activity = Activity::factory()->create();

        Notification::fake();

        $comment = $this->repository->addComment($activity, [
            'body' => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ]);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $comment) {
            return $notification->mentionUrl === "/activities/{$activity->id}?comment_id={$comment->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_comment_is_updated()
    {
        $this->signIn();

        $user     = $this->createUser();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'comment']);

        Notification::fake();

        $comment = $this->repository->update([
            'body' => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ], $comment->id);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $comment) {
            return $notification->mentionUrl === "/activities/{$activity->id}?comment_id={$comment->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_comment_is_created_via_resource()
    {
        $this->signIn();

        $user     = $this->createUser();
        $activity = Activity::factory()->create();

        Notification::fake();

        $comment = $this->repository->addComment($activity, [
            'via_resource'    => 'activities',
            'via_resource_id' => $activity->id,
            'body'            => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ]);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $comment) {
            return $notification->mentionUrl === "/activities/{$activity->id}?comment_id={$comment->id}&section=activities&resourceId={$activity->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_comment_is_updated_via_resource()
    {
        $this->signIn();

        $user     = $this->createUser();
        $activity = Activity::factory()->create();
        $comment  = $activity->comments()->create(['body' => 'comment']);

        Notification::fake();

        $comment = $this->repository->update([
            'via_resource'    => 'activities',
            'via_resource_id' => $activity->id,
            'body'            => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ], $comment->id);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $comment) {
            return $notification->mentionUrl === "/activities/{$activity->id}?comment_id={$comment->id}&section=activities&resourceId={$activity->id}";
        });
    }
}
