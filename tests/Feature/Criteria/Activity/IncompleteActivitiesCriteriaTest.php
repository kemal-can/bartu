<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\IncompleteActivitiesCriteria;

class IncompleteActivitiesCriteriaTest extends TestCase
{
    public function test_incomplete_activities_criteria()
    {
        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(IncompleteActivitiesCriteria::class);
        Activity::factory()->inProgress()->create();
        Activity::factory()->completed()->create();

        $this->assertCount(1, $repository->all());
    }
}
