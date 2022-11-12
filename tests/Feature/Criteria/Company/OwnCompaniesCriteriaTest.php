<?php

namespace Tests\Feature\Criteria\Company;

use Tests\TestCase;
use App\Models\Company;
use Database\Seeders\PermissionsSeeder;
use App\Criteria\Company\OwnCompaniesCriteria;
use App\Contracts\Repositories\CompanyRepository;

class OwnCompaniesCriteriaTest extends TestCase
{
    public function test_own_companies_criteria_queries_only_own_companies()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own companies')->createUser();

        $repository = app(CompanyRepository::class);
        $repository->pushCriteria(OwnCompaniesCriteria::class);

        Company::factory()->for($user)->create();
        Company::factory()->create();

        $this->signIn($user);
        $this->assertCount(1, $repository->all());
    }

    public function test_it_returns_all_companies_when_user_is_authorized_to_see_all_companies()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view all companies')->createUser();

        $repository = app(CompanyRepository::class);
        $repository->pushCriteria(OwnCompaniesCriteria::class);
        Company::factory()->for($user)->create();
        Company::factory()->create();

        $this->signIn($user);
        $this->assertCount(2, $repository->all());

        $this->signIn();
        $this->assertCount(2, $repository->all());
    }
}
