<?php

namespace Tests\Feature\Controller\Api\Workflow;

use Tests\TestCase;
use App\Innoclapps\Workflow\Workflows;
use Database\Seeders\ActivityTypeSeeder;

class WorkflowTriggersControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_workflow_triggers_endpoints()
    {
        $this->getJson('/api/workflows/triggers')->assertUnauthorized();
    }

    public function test_workflow_triggers_can_be_retrieved()
    {
        $this->seed(ActivityTypeSeeder::class);

        $this->signIn();

        $this->getJson('/api/workflows/triggers')
            ->assertJsonCount(Workflows::triggersInstance()->count());
    }
}
