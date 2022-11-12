<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Stage;
use App\Contracts\Repositories\StageRepository;

class StageRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(StageRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_can_properly_retrieve_all_stages_for_option_fields()
    {
        $user = $this->signIn();

        Stage::factory()->count(5)->create();

        $options = $this->repository->allStagesForOptions($user);

        $this->assertCount(5, $options);
        $this->assertArrayHasKey('id', $options[0]);
        $this->assertArrayHasKey('name', $options[0]);
    }

    public function test_it_cannot_delete_stage_with_deals()
    {
        $pipeline = Stage::factory()->has(Deal::factory()->count(2))->create();

        $this->expectExceptionMessage(__('deal.stage.delete_usage_warning'));

        $this->repository->delete($pipeline->id);
    }
}
