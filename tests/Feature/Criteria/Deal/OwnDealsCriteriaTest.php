<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Criteria\Deal\OwnDealsCriteria;
use Database\Seeders\PermissionsSeeder;
use App\Contracts\Repositories\DealRepository;

class OwnDealsCriteriaTest extends TestCase
{
    public function test_own_deals_criteria_queries_only_own_deals()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own deals')->createUser();

        $repository = app(DealRepository::class);
        $repository->pushCriteria(OwnDealsCriteria::class);

        Deal::factory()->for($user)->create();
        Deal::factory()->create();

        $this->signIn($user);
        $this->assertCount(1, $repository->all());
    }

    public function test_it_returns_all_deals_when_user_is_authorized_to_see_all_deals()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view all deals')->createUser();

        $repository = app(DealRepository::class);
        $repository->pushCriteria(OwnDealsCriteria::class);
        Deal::factory()->for($user)->create();
        Deal::factory()->create();

        $this->signIn($user);
        $this->assertCount(2, $repository->all());

        $this->signIn();
        $this->assertCount(2, $repository->all());
    }
}
