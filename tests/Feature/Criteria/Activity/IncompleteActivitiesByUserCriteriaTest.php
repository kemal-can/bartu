<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\IncompleteActivitiesByUserCriteria;

class IncompleteActivitiesByUserCriteriaTest extends TestCase
{
    public function test_incomplete_activities_by_user_criteria()
    {
        $user = $this->signIn();

        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(IncompleteActivitiesByUserCriteria::class);
        Activity::factory()->for($user)->inProgress()->create();
        Activity::factory()->for($user)->completed()->create();
        Activity::factory()->create();

        $this->assertCount(1, $repository->all());
    }
}
