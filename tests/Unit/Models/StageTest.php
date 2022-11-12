<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Stage;
use App\Models\Pipeline;

class StageTest extends TestCase
{
    public function test_stage_has_deals()
    {
        $stage = Stage::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $stage->deals);
    }

    public function test_stage_has_pipeline()
    {
        $stage = Stage::factory()->for(Pipeline::factory())->create();

        $this->assertInstanceOf(Pipeline::class, $stage->pipeline);
    }
}
