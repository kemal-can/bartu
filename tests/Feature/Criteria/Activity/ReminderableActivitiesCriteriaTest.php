<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\ReminderableActivitiesCriteria;

class ReminderableActivitiesCriteriaTest extends TestCase
{
    public function test_reminderable_activities_criteria()
    {
        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(ReminderableActivitiesCriteria::class);

        Activity::factory()->create([
            'due_date'                => date('Y-m-d', strtotime('+30 minutes')),
            'due_time'                => date('H:i:s', strtotime('+30 minutes')),
            'reminder_minutes_before' => 30,
        ]);

        Activity::factory()->noReminder()->create([
            'due_date' => date('Y-m-d', strtotime('+30 minutes')),
            'due_time' => date('H:i:s', strtotime('+30 minutes')),
        ]);

        $this->assertCount(1, $repository->all());
    }
}
