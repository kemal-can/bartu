<?php

namespace Tests\Feature\Criteria\Activity;

use Tests\TestCase;
use App\Models\Activity;
use Database\Seeders\PermissionsSeeder;
use App\Criteria\Activity\OwnActivitiesCriteria;
use App\Contracts\Repositories\ActivityRepository;

class OwnActivitiesCriteriaTest extends TestCase
{
    public function test_own_activities_criteria_queries_only_own_activities()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own activities')->createUser();

        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(OwnActivitiesCriteria::class);

        Activity::factory()->for($user)->create();
        Activity::factory()->create();

        $this->signIn($user);
        $this->assertCount(1, $repository->all());
    }

    public function test_it_returns_all_activities_when_user_is_authorized_to_see_all_activities()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view all activities')->createUser();

        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(OwnActivitiesCriteria::class);
        Activity::factory()->for($user)->create();
        Activity::factory()->create();

        $this->signIn($user);
        $this->assertCount(2, $repository->all());

        $this->signIn();
        $this->assertCount(2, $repository->all());
    }

    public function test_it_retrieves_the_activities_where_user_attends_to_and_are_owned_by()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view attends and owned activities')->createUser();

        $repository = app(ActivityRepository::class);
        $repository->pushCriteria(OwnActivitiesCriteria::class);
        Activity::factory()->create();
        Activity::factory()->for($user)->create();

        $attendsActivity = Activity::factory()->create();
        $guest           = $user->guests()->create([]);
        $guest->activities()->attach($attendsActivity);

        $this->signIn($user);
        $this->assertCount(2, $repository->all());
    }
}
