<?php

namespace Tests\Feature\Criteria\Product;

use Tests\TestCase;
use App\Models\Product;
use Database\Seeders\PermissionsSeeder;
use App\Criteria\Product\OwnProductsCriteria;
use App\Contracts\Repositories\ProductRepository;

class OwnProductsCriteriaTest extends TestCase
{
    public function test_own_products_criteria_queries_only_own_products()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view own products')->createUser();

        $repository = app(ProductRepository::class);
        $repository->pushCriteria(OwnProductsCriteria::class);

        Product::factory()->for($user, 'creator')->create();
        Product::factory()->create();

        $this->signIn($user);
        $this->assertCount(1, $repository->all());
    }

    public function test_it_returns_all_products_when_user_is_authorized_to_see_all_products()
    {
        $this->seed(PermissionsSeeder::class);
        $user = $this->asRegularUser()->withPermissionsTo('view all products')->createUser();

        $repository = app(ProductRepository::class);
        $repository->pushCriteria(OwnProductsCriteria::class);
        Product::factory()->for($user, 'creator')->create();
        Product::factory()->create();

        $this->signIn($user);
        $this->assertCount(2, $repository->all());

        $this->signIn();
        $this->assertCount(2, $repository->all());
    }
}
