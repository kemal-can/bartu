<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\UpcomingActivitiesCriteria;

class UpcomingActivitiesCriteriaTest extends TestCase
{
    public function test_upcoming_activities_criteria()
    {
        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(UpcomingActivitiesCriteria::class);

        Activity::factory()->create([
            'due_date' => date('Y-m-d', strtotime('+1 week')),
            'due_time' => date('H:i:s', strtotime('+1 week')),
        ]);

        Activity::factory()->create([
            'due_date' => date('Y-m-d', strtotime('+1 week')),
            'due_time' => null,
        ]);

        Activity::factory()->create([
            'due_date' => date('Y-m-d', strtotime('-1 week')),
            'due_time' => date('H:i:s', strtotime('-1 week')),
        ]);

        Activity::factory()->create([
            'due_date' => date('Y-m-d', strtotime('-1 week')),
            'due_time' => null,
        ]);

        $this->assertCount(2, $repository->all());
    }
}
