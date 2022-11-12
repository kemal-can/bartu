<?php

namespace Tests\Feature\Resource\Product\Actions;

use Database\Seeders\PermissionsSeeder;
use Tests\Feature\Resource\ResourceTestCase;
use App\Resources\Product\Actions\UpdateUnitPrice;

class UpdateUnitPriceTest extends ResourceTestCase
{
    protected $action;

    protected $resourceName = 'products';

    protected function setUp() : void
    {
        parent::setUp();
        $this->action = new UpdateUnitPrice;
    }

    protected function tearDown() : void
    {
        unset($this->action);
        parent::tearDown();
    }

    public function test_super_admin_user_can_run_update_unit_price_action()
    {
        $this->signIn();
        $product = $this->factory()->create(['unit_price' => 1000]);

        $this->postJson($this->actionEndpoint($this->action), [
            'unit_price' => 2000,
            'ids'        => [$product->id],
        ])->assertOk();

        $this->assertEquals(2000, $product->fresh()->unit_price);
    }

    public function test_authorized_user_can_run_update_unit_price_action()
    {
        $this->seed(PermissionsSeeder::class);
        $this->asRegularUser()->withPermissionsTo('edit all products')->signIn();

        $user    = $this->createUser();
        $product = $this->factory()->for($user, 'creator')->create(['unit_price' => 1000]);

        $this->postJson($this->actionEndpoint($this->action), [
            'unit_price' => 2000,
            'ids'        => [$product->id],
        ])->assertOk();

        $this->assertEquals(2000, $product->fresh()->unit_price);
    }

    public function test_unauthorized_user_can_run_update_unit_price_action_on_own_product()
    {
        $this->seed(PermissionsSeeder::class);
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own products')->signIn();
        $this->createUser();

        $productForSignedIn = $this->factory()->for($signedInUser, 'creator')->create(['unit_price' => 1000]);
        $otherProduct       = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'unit_price' => 2000,
            'ids'        => [$otherProduct->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->postJson($this->actionEndpoint($this->action), [
            'unit_price' => 2000,
            'ids'        => [$productForSignedIn->id],
        ]);
        $this->assertEquals(2000, $productForSignedIn->fresh()->unit_price);
    }

    public function test_update_unit_price_action_requires_price()
    {
        $this->signIn();
        $this->createUser();
        $product = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'unit_price' => '',
            'ids'        => [$product->id],
        ])->assertJsonValidationErrors('unit_price');
    }
}
