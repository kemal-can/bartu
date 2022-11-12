<?php

namespace Tests\Feature\Resource\Contact\Actions;

use Database\Seeders\PermissionsSeeder;
use App\Resources\Actions\AssignOwnerAction;
use Tests\Feature\Resource\ResourceTestCase;

class ContactAssignOwnerTest extends ResourceTestCase
{
    protected $action;

    protected $resourceName = 'contacts';

    protected function setUp() : void
    {
        parent::setUp();
        $this->action = new AssignOwnerAction;
    }

    protected function tearDown() : void
    {
        unset($this->action);
        parent::tearDown();
    }

    public function test_super_admin_user_can_run_contact_assign_owner_action()
    {
        $this->signIn();
        $user    = $this->createUser();
        $contact = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids'     => [$contact->id],
        ])->assertOk();

        $this->assertEquals($user->id, $contact->fresh()->user_id);
    }

    public function test_authorized_user_can_run_contact_assign_owner_action()
    {
        $this->seed(PermissionsSeeder::class);
        $this->asRegularUser()->withPermissionsTo('edit all contacts')->signIn();

        $user    = $this->createUser();
        $contact = $this->factory()->for($user)->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids'     => [$contact->id],
        ])->assertOk();

        $this->assertEquals($user->id, $contact->fresh()->user_id);
    }

    public function test_unauthorized_user_can_run_contact_assign_owner_action_on_own_contact()
    {
        $this->seed(PermissionsSeeder::class);
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own contacts')->signIn();
        $user         = $this->createUser();

        $contactForSignedIn = $this->factory()->for($signedInUser)->create();
        $otherContact       = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids'     => [$otherContact->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids'     => [$contactForSignedIn->id],
        ]);

        $this->assertEquals($user->id, $contactForSignedIn->fresh()->user_id);
    }

    public function test_contact_assign_owner_action_requires_owner()
    {
        $this->signIn();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [],
        ])->assertJsonValidationErrors(['user_id']);
    }
}
