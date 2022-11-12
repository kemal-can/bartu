<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Pipeline;

class PipelineTest extends TestCase
{
    public function test_pipeline_can_be_primary()
    {
        $pipeline = Pipeline::factory()->primary()->create();

        $this->assertTrue($pipeline->isPrimary());
    }

    public function test_pipeline_has_default_sort_data()
    {
        $pipeline = Pipeline::factory()->create();

        $pipeline->setDefaultSortData(['direction' => 'asc', 'field' => 'created_at'], 1);

        $this->assertEquals(['direction' => 'asc', 'field' => 'created_at'], $pipeline->getDefaultSortData(1));
    }

    public function test_pipeline_has_default_sort_data_attribute_for_the_logged_in_user()
    {
        $user     = $this->signIn();
        $pipeline = Pipeline::factory()->create();

        $pipeline->setDefaultSortData(['direction' => 'asc', 'field' => 'created_at'], $user->id);

        $this->assertEquals(['direction' => 'asc', 'field' => 'created_at'], $pipeline->default_sort_data);
    }

    public function test_pipeline_has_deals()
    {
        $pipeline = Pipeline::factory()->withStages()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $pipeline->deals);
    }
}
