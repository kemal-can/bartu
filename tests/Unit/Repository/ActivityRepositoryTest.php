<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Contact;
use App\Models\Activity;
use App\Models\ActivityType;
use Tests\Concerns\TestsMentions;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Contracts\Repositories\ActivityRepository;

class ActivityRepositoryTest extends TestCase
{
    use TestsMentions;

    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(ActivityRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_uses_default_activity_type_when_activity_type_is_empty()
    {
        $this->signIn();
        $type = ActivityType::factory()->create();
        ActivityType::setDefault($type->id);

        $attributes = Activity::factory()->raw();
        unset($attributes['activity_type_id']);
        $activity = $this->repository->create($attributes);

        $this->assertEquals($type->id, $activity->activity_type_id);
    }

    public function test_it_can_mark_the_activity_as_completed_on_creation()
    {
        $this->signIn();
        $attributes = Activity::factory()->raw(['is_completed' => true]);

        $activity = $this->repository->create($attributes);

        $this->assertTrue($activity->isCompleted);
    }

    public function test_it_can_mark_the_activity_as_completed_on_update()
    {
        $this->signIn();

        $activity = Activity::factory()->create();

        $activity = $this->repository->update([
            'is_completed' => true,
        ], $activity->id);

        $this->assertTrue($activity->isCompleted);
    }

    public function test_it_can_mark_the_activity_as_incompleted_on_update()
    {
        $this->signIn();

        $activity = Activity::factory()->completed()->create();

        $activity = $this->repository->update([
            'is_completed' => false,
        ], $activity->id);

        $this->assertFalse($activity->isCompleted);
    }

    public function test_activity_guests_can_be_saved_on_creation()
    {
        $user       = $this->signIn();
        $contact    = Contact::factory()->create();
        $attributes = Activity::factory()->raw();

        $attributes['guests'] = [
            'users'    => [$user->id],
            'contacts' => [$contact->id],
        ];

        $activity = $this->repository->create($attributes);

        $this->assertCount(2, $activity->fresh()->guests);
    }

    public function test_it_send_notifications_to_guests()
    {
        $this->signIn();
        $user       = $this->createUser();
        $contact    = Contact::factory()->create();
        $attributes = Activity::factory()->raw();
        settings()->set('send_contact_attends_to_activity_mail', true);

        $attributes['guests'] = [
            'users'    => [$user->id],
            'contacts' => [$contact->id],
        ];

        Mail::fake();
        Notification::fake();

        $this->repository->create($attributes);

        Notification::assertSentTo($user, $user->getAttendeeNotificationClass());
        Mail::assertQueued($contact->getAttendeeNotificationClass(), function ($mail) use ($contact) {
            return $mail->hasTo($contact->email);
        });
    }

    public function test_it_does_not_send_notification_when_current_user_is_added_as_guest()
    {
        $currentUser = $this->signIn();
        $user        = $this->createUser();
        $attributes  = Activity::factory()->raw();

        $attributes['guests'] = [
            'users' => [$user->id, $currentUser->id],
        ];

        Notification::fake();

        $this->repository->create($attributes);

        Notification::assertSentTo($user, $user->getAttendeeNotificationClass());
        Notification::assertNotSentTo($currentUser, $user->getAttendeeNotificationClass());
    }

    public function test_it_does_not_send_notification_when_contact_send_notification_is_false()
    {
        $this->signIn();

        $contact    = Contact::factory()->create();
        $attributes = Activity::factory()->raw();
        settings()->set('send_contact_attends_to_activity_mail', false);

        $attributes['guests'] = [
            'contacts' => [$contact->id],
        ];

        Mail::fake();

        $this->repository->create($attributes);

        Mail::assertNothingSent();
    }

    public function test_activity_guests_can_be_saved_on_update()
    {
        $user     = $this->signIn();
        $contact  = Contact::factory()->create();
        $activity = Activity::factory()->create();

        $activity = $this->repository->update(['guests' => [
            'users'    => [$user->id],
            'contacts' => [$contact->id],
        ]], $activity->id);

        $this->assertCount(2, $activity->guests);

        // Detach
        $activity = $this->repository->update(['guests' => [
            'users' => [$user->id],
        ]], $activity->id);

        $this->assertCount(1, $activity->guests);
    }

    public function test_activity_can_be_marked_as_complete()
    {
        $activity = Activity::factory()->create();

        $activity = $this->repository->complete($activity);

        $this->assertTrue($activity->isCompleted);
    }

    public function test_activity_can_be_marked_as_incomplete()
    {
        $activity = Activity::factory()->completed()->create();

        $activity = $this->repository->incomplete($activity);

        $this->assertFalse($activity->isCompleted);
    }

    public function test_it_can_get_incomplete_activities_by_user()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        Activity::factory()->for($user1)->create();
        Activity::factory()->for($user2)->create();
        Activity::factory()->for($user2)->create();

        $activities = $this->repository->getIncompleteByUser($user2);

        $this->assertCount(2, $activities);
    }

    public function test_due_activities_are_properly_queried()
    {
        $dueDate = now();

        Activity::factory()->create([
            'due_date' => $dueDate->format('Y-m-d'),
            'due_time' => $dueDate->format('H:i'),
        ]);

        $dueDate->addWeek();

        Activity::factory()->create([
            'due_date' => $dueDate->format('Y-m-d'),
            'due_time' => $dueDate->format('H:i'),
        ]);

        $this->assertCount(1, $this->repository->overdue());
    }

    public function test_properly_queries_activities_that_needs_a_reminder_to_be_sent()
    {
        $user    = $this->createUser();
        $dueDate = now();

        $dueDate->addMinutes(30);

        Activity::factory()->for($user)->create([
            'reminder_minutes_before' => 30,
            'due_date'                => $dueDate->format('Y-m-d'),
            'due_time'                => $dueDate->format('H:i'),
        ]);

        $dueDate->addWeek();

        Activity::factory()->for($user)->create([
            'reminder_minutes_before' => 30,
            'due_date'                => $dueDate->format('Y-m-d'),
            'due_time'                => $dueDate->format('H:i'),
        ]);

        $this->assertCount(1, $this->repository->reminderAble());
    }

    public function test_it_send_notifications_to_mentioned_users_when_activity_is_created()
    {
        $this->signIn();

        $user       = $this->createUser();
        $attributes = Activity::factory()->make([
            'note' => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ])->toArray();

        Notification::fake();

        $activity = $this->repository->create($attributes);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity) {
            return $notification->mentionUrl === "/activities/{$activity->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_activity_is_updated()
    {
        $this->signIn();

        $user     = $this->createUser();
        $activity = Activity::factory()->create();

        Notification::fake();

        $activity = $this->repository->update([
            'note' => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ], $activity->id);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity) {
            return $notification->mentionUrl === "/activities/{$activity->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_activity_is_created_via_resource()
    {
        $this->signIn();

        $user       = $this->createUser();
        $contact    = Contact::factory()->create();
        $attributes = Activity::factory()->make([
            'note' => 'Other Text - ' . $this->mentionText($user->id, $user->name),
        ])->toArray();

        Notification::fake();

        $activity = $this->repository->create(array_merge(
            $attributes,
            [
                'via_resource'    => 'contacts',
                'via_resource_id' => $contact->id,
            ]
        ));

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $contact) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=activities&resourceId={$activity->id}";
        });
    }

    public function test_it_send_notifications_to_mentioned_users_when_activity_is_updated_via_resource()
    {
        $this->signIn();

        $user     = $this->createUser();
        $contact  = Contact::factory()->create();
        $activity = Activity::factory()->create();

        Notification::fake();

        $activity = $this->repository->update([
            'note'            => 'Other Text - ' . $this->mentionText($user->id, $user->name),
            'via_resource'    => 'contacts',
            'via_resource_id' => $contact->id,
        ], $activity->id);

        Notification::assertSentTo($user, UserMentioned::class, function ($notification) use ($activity, $contact) {
            return $notification->mentionUrl === "/contacts/{$contact->id}?section=activities&resourceId={$activity->id}";
        });
    }
}
