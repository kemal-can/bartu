<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Pipeline;
use App\Models\ModelVisibilityGroup;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Contracts\Repositories\PipelineRepository;

class VisiblePipelinesCriteriaTest extends TestCase
{
    public function test_visible_pipelines_criteria()
    {
        $user = User::factory()->has(Team::factory())->create();

        Pipeline::factory()
            ->has(
                ModelVisibilityGroup::factory()->teams()->hasAttached($user->teams->first()),
                'visibilityGroup'
            )
            ->create();

        $repository = app(PipelineRepository::class);

        $repository->pushCriteria(new VisiblePipelinesCriteria($user));

        $this->assertCount(1, $repository->all());
    }
}
