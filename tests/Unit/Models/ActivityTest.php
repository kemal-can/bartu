<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Innoclapps\Date\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ActivityTest extends TestCase
{
    public function test_when_activity_created_by_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $activity = Activity::factory(['created_by' => null])->create();

        $this->assertEquals($activity->created_by, $user->id);
    }

    public function test_activity_created_by_can_be_provided()
    {
        $user = $this->createUser();

        $activity = Activity::factory()->for($user, 'creator')->create();

        $this->assertEquals($activity->created_by, $user->id);
    }

    public function test_activity_has_path_attribute()
    {
        $activity = Activity::factory()->create();

        $this->assertEquals('/activities/1', $activity->path);
    }

    public function test_activity_has_display_name_attribute()
    {
        $activity = Activity::factory(['title' => 'Activity title'])->make();

        $this->assertEquals('Activity title', $activity->display_name);
    }

    public function test_activity_has_deals()
    {
        $activity = Activity::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $activity->deals);
    }

    public function test_activity_has_companies()
    {
        $activity = Activity::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $activity->companies);
    }

    public function test_activity_has_contacts()
    {
        $activity = Activity::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $activity->contacts);
    }

    public function test_activity_has_user()
    {
        $activity = Activity::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $activity->user);
    }

    public function test_activity_has_type()
    {
        $activity = Activity::factory()->for(ActivityType::factory(), 'type')->create();

        $this->assertInstanceOf(ActivityType::class, $activity->type);
    }

    public function test_it_can_determine_whether_activity_is_reminded()
    {
        $this->assertTrue(Activity::factory()->reminded()->make()->isReminded);
        $this->assertFalse(Activity::factory()->make(['reminder_minutes_before' => 30])->isReminded);
    }

    public function test_it_can_determine_whether_activity_is_completed()
    {
        $this->assertTrue(Activity::factory()->completed()->make()->isCompleted);
        $this->assertFalse(Activity::factory()->make()->isCompleted);
    }

    public function test_it_can_determine_whether_activity_is_completed_via_attribute()
    {
        $this->assertTrue(Activity::factory()->completed()->make()->is_completed);
        $this->assertFalse(Activity::factory()->make()->is_completed);
    }

    public function test_it_can_determine_whether_activity_is_all_day()
    {
        $this->assertTrue(Activity::factory()->allDay()->make()->isAllDay());
        $this->assertFalse(Activity::factory()->make(['due_time' => '08:00:00', 'end_time' => '09:00:00'])->isAllDay());
    }

    public function test_it_can_determine_whether_activity_is_due()
    {
        $this->assertTrue(Activity::factory()->make([
            'due_date' => now()->subDays(1),
        ])->is_due);

        $this->assertFalse(Activity::factory()->make([
            'due_date' => now()->addDays(1),
        ])->is_due);
    }

    public function test_activity_is_not_due_when_completed()
    {
        $this->assertFalse(Activity::factory()->completed()->make([
            'due_date' => now()->subDays(1),
        ])->is_due);
    }

    public function test_activity_has_full_due_date_attribute()
    {
        $activity = Activity::factory()->make([
            'due_date' => '2021-11-20',
            'due_time' => '08:00:00',
        ]);

        $this->assertEquals('2021-11-20 08:00:00', $activity->full_due_date);

        $activity = Activity::factory()->make([
            'due_date' => '2021-11-20',
            'due_time' => null,
        ]);

        $this->assertEquals('2021-11-20', $activity->full_due_date);
    }

    public function test_activity_has_full_end_date_attribute()
    {
        $activity = Activity::factory()->make([
            'end_date' => '2021-11-20',
            'end_time' => '08:00:00',
        ]);

        $this->assertEquals('2021-11-20 08:00:00', $activity->full_end_date);

        $activity = Activity::factory()->make([
            'end_date' => '2021-11-20',
            'end_time' => null,
        ]);

        $this->assertEquals('2021-11-20', $activity->full_end_date);
    }

    public function test_reminder_at_attribute_is_set_when_creating_activity()
    {
        $activity = Activity::factory()->create(['reminder_minutes_before' => 30]);

        $this->assertNotNull($activity->reminder_at);

        $activity = Activity::factory()->noReminder()->create();

        $this->assertNull($activity->reminder_at);
    }

    public function test_reminder_at_attribute_is_set_when_updating_activity()
    {
        $activity = Activity::factory()->create(['reminder_minutes_before' => 30]);

        $activity->reminder_minutes_before = null;
        $activity->save();

        $this->assertNull($activity->reminder_at);

        $activity->reminder_minutes_before = 30;
        $activity->save();

        $this->assertNotNull($activity->reminder_at);
    }

    public function test_reminder_at_is_not_updated_if_reminder_minutes_before_is_not_provided()
    {
        $activity           = Activity::factory()->create(['reminder_minutes_before' => 30]);
        $originalReminderAt = $activity->reminder_at->format('Y-m-d H:i:s');
        unset($activity['reminder_minutes_before']);

        $activity->save();

        $this->assertSame($originalReminderAt, $activity->fresh()->reminder_at->format('Y-m-d H:i:s'));
    }

    public function test_it_resets_the_reminded_at_attribute_when_reminder_minutes_before_is_changed()
    {
        $activity = Activity::factory()->create([
            'reminder_minutes_before' => 30,
            'reminded_at'             => now(),
        ]);

        $activity->reminder_minutes_before = null;
        $activity->save();

        $this->assertNull($activity->reminded_at);

        $activity = Activity::factory()->create([
            'reminder_minutes_before' => 30,
            'reminded_at'             => now(),
        ]);

        $activity->reminder_minutes_before = 60;
        $activity->save();

        $this->assertNull($activity->reminded_at);
    }

    public function test_reminder_at_date_is_properly_determined()
    {
        $activity = Activity::factory()->for(User::factory()->state(['timezone' => 'UTC']))->create([
            'reminder_minutes_before' => 30,
            'due_date'                => '2021-12-09',
            'due_time'                => '12:00:00',
        ]);

        $this->assertTrue(Carbon::parse('2021-12-09 11:30:00')->eq($activity->reminder_at));

        $user = User::factory()->state(['timezone' => 'Europe/Berlin'])->create();
        $date = Carbon::inUserTimezone('2021-12-15 17:30:00', $user);

        $activity = Activity::factory()->for($user)->create([
            'reminder_minutes_before' => 30,
            'due_date'                => $date->copy()->inAppTimezone()->format('Y-m-d'),
            'due_time'                => $date->copy()->inAppTimezone()->format('H:i:s'),
        ]);

        $this->assertTrue(
            Carbon::inUserTimezone('2021-12-15 17:00:00')->inAppTimezone()->eq($activity->reminder_at)
        );

        $activity = Activity::factory()->for(
            User::factory()->state(['timezone' => 'Europe/Berlin'])
        )
            ->create([
            'reminder_minutes_before' => 30,
            'due_date'                => '2021-12-09',
            'due_time'                => null,
        ]);

        $this->assertTrue(Carbon::parse('2021-12-08 22:30:00')->eq($activity->reminder_at));

        $activity = Activity::factory()->for(
            User::factory()->state(['timezone' => 'America/New_York'])
        )
            ->create([
                'reminder_minutes_before' => 30,
                'due_date'                => '2021-12-17',
                'due_time'                => null,
            ]);

        $this->assertEquals('2021-12-17 04:30:00', $activity->reminder_at->format('Y-m-d H:i:s'));
    }

    public function test_activity_has_comments()
    {
        $activity = new Activity;

        $this->assertInstanceof(MorphMany::class, $activity->comments());
    }
}
