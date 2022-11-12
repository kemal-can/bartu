<?php

namespace Tests\Feature\Resource\Activity\Actions;

use Database\Seeders\PermissionsSeeder;
use Tests\Feature\Resource\ResourceTestCase;

class ActivityDeleteActionsTest extends ResourceTestCase
{
    protected $resourceName = 'activities';

    public function test_super_admin_user_can_run_activity_delete_action()
    {
        $this->signIn();
        $user     = $this->createUser();
        $activity = $this->factory()->for($user)->create();
        $action   = $this->findAction('delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activity->id],
        ])->assertOk();

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_authorized_user_can_run_activity_delete_action()
    {
        $this->seed(PermissionsSeeder::class);

        $this->asRegularUser()->withPermissionsTo('delete any activity')->signIn();

        $this->createUser();
        $activity = $this->factory()->create();
        $action   = $this->findAction('delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activity->id],
        ])->assertOk();

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_authorized_user_can_run_activity_delete_action_only_on_own_activities()
    {
        $this->seed(PermissionsSeeder::class);

        $signedInUser = $this->asRegularUser()->withPermissionsTo('delete own activities')->signIn();
        $this->createUser();

        $activityForSignedIn = $this->factory()->create(['user_id' => $signedInUser->id]);
        $otherActivity       = $this->factory()->create();

        $action = $this->findAction('delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$otherActivity->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->assertDatabaseHas('activities', ['id' => $otherActivity->id]);

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activityForSignedIn->id],
        ]);

        $this->assertSoftDeleted('activities', ['id' => $activityForSignedIn->id]);
    }

    public function test_unauthorized_user_can_run_activity_delete_action_on_own_activity()
    {
        $this->seed(PermissionsSeeder::class);

        $signedInUser = $this->asRegularUser()->withPermissionsTo('delete own activities')->signIn();
        $user         = $this->createUser();

        $activityForSignedIn = $this->factory()->create(['user_id' => $signedInUser->id]);
        $otherActivity       = $this->factory()->for($user)->create();

        $action = $this->findAction('delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$otherActivity->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->assertDatabaseHas('activities', ['id' => $otherActivity->id]);

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activityForSignedIn->id],
        ]);

        $this->assertSoftDeleted('activities', ['id' => $activityForSignedIn->id]);
    }

    public function test_super_super_admin_user_can_run_activity_bulk_delete_action()
    {
        $this->signIn();
        $user     = $this->createUser();
        $activity = $this->factory()->for($user)->create();
        $action   = $this->findAction('bulk-delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activity->id],
        ])->assertOk();

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_authorized_user_can_run_activity_bulk_delete_action()
    {
        $this->seed(PermissionsSeeder::class);

        $this->asRegularUser()->withPermissionsTo('bulk delete activities')->signIn();

        $user     = $this->createUser();
        $activity = $this->factory()->for($user)->create();
        $action   = $this->findAction('bulk-delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activity->id],
        ])->assertOk();

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_unauthorized_user_cant_run_activity_bulk_delete_action()
    {
        $this->asRegularUser()->signIn();
        $user     = $this->createUser();
        $activity = $this->factory()->for($user)->create();
        $action   = $this->findAction('bulk-delete');

        $this->postJson($this->actionEndpoint($action), [
            'ids' => [$activity->id],
        ])->assertJson(['error' => __('user.not_authorized')]);

        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
    }
}
