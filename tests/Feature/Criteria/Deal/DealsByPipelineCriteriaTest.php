<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Contracts\Repositories\DealRepository;
use App\Criteria\Deal\DealsByPipelineCriteria;

class DealsByPipelineCriteriaTest extends TestCase
{
    public function test_deals_by_pipeline_criteria()
    {
        $pipeline = Pipeline::factory()->withStages()->create();
        Deal::factory()->for($pipeline)->create();
        Deal::factory()->create();

        $repository = app(DealRepository::class);

        $repository->pushCriteria(new DealsByPipelineCriteria($pipeline->id));

        $this->assertCount(1, $repository->all());
    }
}
