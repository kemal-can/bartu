<?php

namespace Tests\Feature\Resource\Deal\Actions;

use App\Enums\DealStatus;
use Database\Seeders\PermissionsSeeder;
use App\Resources\Deal\Actions\MarkAsLost;
use Tests\Feature\Resource\ResourceTestCase;

class MarkAsLostTest extends ResourceTestCase
{
    protected $action;

    protected $resourceName = 'deals';

    protected function setUp() : void
    {
        parent::setUp();
        $this->action = new MarkAsLost;
    }

    protected function tearDown() : void
    {
        unset($this->action);
        parent::tearDown();
    }

    public function test_super_admin_user_can_run_deal_mark_as_lost_action()
    {
        $this->signIn();
        $deal = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$deal->id],
        ])->assertOk();

        $this->assertSame(DealStatus::lost, $deal->fresh()->status);
    }

    public function test_authorized_user_can_run_deal_mark_as_lost_action()
    {
        $this->seed(PermissionsSeeder::class);
        $this->asRegularUser()->withPermissionsTo('edit all deals')->signIn();

        $user = $this->createUser();
        $deal = $this->factory()->for($user)->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$deal->id],
        ])->assertOk();

        $this->assertSame(DealStatus::lost, $deal->fresh()->status);
    }

    public function test_unauthorized_user_can_run_deal_mark_as_lost_action_on_own_deal()
    {
        $this->seed(PermissionsSeeder::class);
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own deals')->signIn();
        $this->createUser();

        $dealForSignedIn = $this->factory()->for($signedInUser)->create();
        $otherDeal       = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$otherDeal->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [$dealForSignedIn->id],
        ]);

        $this->assertSame(DealStatus::lost, $dealForSignedIn->fresh()->status);
    }
}
