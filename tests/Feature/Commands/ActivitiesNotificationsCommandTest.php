<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\Activity;
use App\Notifications\ActivityReminder;
use Illuminate\Support\Facades\Notification;

class ActivitiesNotificationsCommandTest extends TestCase
{
    public function test_activities_notifications_command()
    {
        Notification::fake();

        $activity = Activity::factory()->create([
            'due_date'                => date('Y-m-d', strtotime('+30 minutes')),
            'due_time'                => date('H:i:s', strtotime('+30 minutes')),
            'reminder_minutes_before' => 30,
        ]);

        $this->artisan('bartu:activities-notification')
            ->assertSuccessful();

        Notification::assertSentTo($activity->user, ActivityReminder::class);
        Notification::assertSentToTimes($activity->user, ActivityReminder::class, 1);

        $this->assertNotNull($activity->fresh()->reminded_at);
    }
}
