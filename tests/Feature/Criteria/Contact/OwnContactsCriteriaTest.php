<?php

namespace Tests\Feature\Criteria\Contact;

use Tests\TestCase;
use App\Models\Contact;
use Database\Seeders\PermissionsSeeder;
use App\Criteria\Contact\OwnContactsCriteria;
use App\Contracts\Repositories\ContactRepository;

class OwnContactsCriteriaTest extends TestCase
{
    public function test_own_contacts_criteria_queries_only_own_contacts()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->createUser();

        $repository = app(ContactRepository::class);
        $repository->pushCriteria(OwnContactsCriteria::class);

        Contact::factory()->for($user)->create();
        Contact::factory()->create();

        $this->signIn($user);
        $this->assertCount(1, $repository->all());
    }

    public function test_it_returns_all_contacts_when_user_is_authorized_to_see_all_contacts()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view all contacts')->createUser();

        $repository = app(ContactRepository::class);
        $repository->pushCriteria(OwnContactsCriteria::class);
        Contact::factory()->for($user)->create();
        Contact::factory()->create();

        $this->signIn($user);
        $this->assertCount(2, $repository->all());

        $this->signIn();
        $this->assertCount(2, $repository->all());
    }
}
