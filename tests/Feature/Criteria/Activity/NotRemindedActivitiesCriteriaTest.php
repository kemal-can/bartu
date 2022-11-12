<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\NotRemindedActivitiesCriteria;

class NotRemindedActivitiesCriteriaTest extends TestCase
{
    public function test_not_reminded_activities_criteria()
    {
        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(NotRemindedActivitiesCriteria::class);
        Activity::factory()->reminded()->create();
        Activity::factory()->create();

        $this->assertCount(1, $repository->all());
    }
}
