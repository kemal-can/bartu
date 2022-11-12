<?php

namespace Tests\Feature\Criteria;

use Tests\TestCase;
use App\Models\Contact;
use App\Criteria\QueriesByUserCriteria;
use App\Contracts\Repositories\ContactRepository;

class QueriesByUserCriteriaTest extends TestCase
{
    public function test_it_uses_by_default_the_current_logged_in_user()
    {
        $user       = $this->signIn($this->createUser());
        $repository = app(ContactRepository::class);
        $repository->pushCriteria(QueriesByUserCriteria::class);
        Contact::factory()->count(2)->create();
        Contact::factory()->for($user)->create();

        $this->assertCount(1, $repository->all());
    }

    public function test_it_uses_the_provided_user()
    {
        $user       = $this->signIn($this->createUser());
        $user2      = $this->createUser();
        $repository = app(ContactRepository::class);
        $repository->pushCriteria(new QueriesByUserCriteria($user2));

        Contact::factory()->for($user)->count(2)->create();
        Contact::factory()->for($user2)->create();

        $this->assertCount(1, $repository->all());

        $repository->resetCriteria()->pushCriteria(new QueriesByUserCriteria($user2->id));
        $this->assertCount(1, $repository->all());
    }

    public function test_it_accepts_custom_column_name()
    {
        $user       = $this->signIn($this->createUser());
        $user2      = $this->createUser();
        $repository = app(ContactRepository::class);
        $repository->pushCriteria(new QueriesByUserCriteria($user, 'created_by'));

        Contact::factory()->count(2)->for($user2, 'creator')->create();
        Contact::factory()->for($user, 'creator')->create();

        $this->assertCount(1, $repository->all());
    }
}
