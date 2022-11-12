<?php

namespace Tests\Feature\Resource\Product\Actions;

use Database\Seeders\PermissionsSeeder;
use Tests\Feature\Resource\ResourceTestCase;
use App\Resources\Product\Actions\MarkAsInactive;

class MarkAsInactiveTest extends ResourceTestCase
{
    protected $action;

    protected $resourceName = 'products';

    protected function setUp() : void
    {
        parent::setUp();
        $this->action = new MarkAsInactive;
    }

    protected function tearDown() : void
    {
        unset($this->action);
        parent::tearDown();
    }

    public function test_super_admin_user_can_run_mark_as_inactive_action()
    {
        $this->signIn();
        $product = $this->factory()->active()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$product->id],
        ])->assertOk();

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_authorized_user_can_run_mark_as_inactive_action()
    {
        $this->seed(PermissionsSeeder::class);
        $this->asRegularUser()->withPermissionsTo('edit all products')->signIn();

        $user    = $this->createUser();
        $product = $this->factory()->active()->for($user, 'creator')->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$product->id],
        ])->assertOk();

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_unauthorized_user_can_run_mark_as_inactive_action_on_own_deal()
    {
        $this->seed(PermissionsSeeder::class);
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own products')->signIn();
        $this->createUser();

        $productForSignedIn = $this->factory()->active()->for($signedInUser, 'creator')->create();
        $otherProduct       = $this->factory()->active()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$otherProduct->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$productForSignedIn->id],
        ]);

        $this->assertFalse($productForSignedIn->fresh()->is_active);
    }
}
