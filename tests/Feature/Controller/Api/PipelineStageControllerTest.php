<?php

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;
use App\Models\Pipeline;

class PipelineStageControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_pipeline_stages_endpoints()
    {
        $this->getJson('/api/pipelines/FAKE_ID/stages')->assertUnauthorized();
    }

    public function test_user_can_retrieve_pipeline_stages()
    {
        $this->signIn();

        $pipeline = Pipeline::factory()->withStages([['name' => 'Stage Name', 'win_probability' => 20]])->create();

        $this->getJson("/api/pipelines/{$pipeline->id}/stages")
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Stage Name');
    }
}
