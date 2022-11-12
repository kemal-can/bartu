<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Stage;
use App\Models\Pipeline;
use App\Enums\DealStatus;
use App\Events\DealMovedToStage;
use Illuminate\Support\Facades\Event;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;

class DealRepositoryTest extends TestCase
{
    protected $repository;

    protected $stageRepository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository      = app(DealRepository::class);
        $this->stageRepository = app(StageRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository, $this->stageRepository);

        parent::tearDown();
    }

    public function test_when_creating_it_uses_stage_pipeline_when_pipeline_is_not_provided()
    {
        $deal = $this->repository->create([
            'name'     => 'Deal Name',
            'stage_id' => Stage::factory()->create()->id,
        ]);

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_status_can_be_provided_when_creating_new_deal()
    {
        $deal = $this->repository->create([
            'name'     => 'Deal Name',
            'stage_id' => Stage::factory()->create()->id,
            'status'   => DealStatus::lost,
        ]);

        $this->assertEquals(DealStatus::lost, $deal->status);

        // string
        $deal = $this->repository->create([
            'name'     => 'Deal Name',
            'stage_id' => Stage::factory()->create()->id,
            'status'   => DealStatus::won->name,
        ]);

        $this->assertEquals(DealStatus::won, $deal->status);
    }

    public function test_status_can_be_provided_when_updating_a_deal()
    {
        $deal = Deal::factory()->open()->create();

        $updated = $this->repository->update([
            'status' => DealStatus::lost,
        ], $deal->id);

        $this->assertEquals(DealStatus::lost, $updated->status);

        // String
        $updated = $this->repository->update([
            'status' => DealStatus::won->name,
        ], $deal->id);

        $this->assertEquals(DealStatus::won, $updated->status);
    }

    public function test_when_updating_it_uses_stage_pipeline_when_pipeline_is_not_provided()
    {
        $pipeline = Pipeline::factory()->has(Stage::factory())->create();

        $deal = Deal::factory([
            'pipeline_id' => $pipeline->id,
            'stage_id'    => $pipeline->stages[0]->id,
        ])->create();

        $updated = $this->repository->update([
            'pipeline_id' => null,
            'stage_id'    => $deal->stage_id,
        ], $deal->id);

        $this->assertEquals($updated->stage->pipeline_id, $updated->pipeline_id);
    }

    public function test_when_creating_it_uses_stage_pipeline_when_provided_pipeline_id_does_not_belong_to_the_stage()
    {
        $otherPipeline = Pipeline::factory()->create();
        $mainPipeline  = Pipeline::factory()->has(Stage::factory())->create();

        $deal = $this->repository->create([
            'name'        => 'Deal Name',
            'pipeline_id' => $otherPipeline->id,
            'stage_id'    => $mainPipeline->stages[0]->id,
        ]);

        $this->assertEquals($deal->stage->pipeline_id, $deal->pipeline_id);
    }

    public function test_when_updating_it_uses_stage_pipeline_id_when_provided_pipeline_id_does_not_belong_to_the_stage()
    {
        $otherPipeline = Pipeline::factory()->create();
        $deal          = Deal::factory()->for(Pipeline::factory()->has(Stage::factory()))->create();

        $updated = $this->repository->update([
            'pipeline_id' => $otherPipeline->id,
            'stage_id'    => $deal->pipeline->stages[0]->id,
        ], $deal->id);

        $this->assertEquals($updated->stage->pipeline_id, $updated->pipeline_id);
    }

    public function test_moved_to_stage_event_is_triggered_when_deal_stage_is_updated()
    {
        $deal    = Deal::factory()->create();
        $stageId = $this->stageRepository->findWhereNotIn('id', [$deal->stage_id])->first()->id;

        Event::fake();
        $this->repository->update(['stage_id' => $stageId], $deal->id);

        Event::assertDispatched(DealMovedToStage::class);
    }

    public function test_stage_moved_activity_is_logged_when_deal_stage_is_updated()
    {
        $deal    = Deal::factory()->create();
        $stageId = $this->stageRepository->findWhereNotIn('id', [$deal->stage_id])->first()->id;

        $this->repository->update(['stage_id' => $stageId], $deal->id);

        $latestActivity = $deal->changelog()->orderBy('id', 'desc')->first();
        $this->assertStringContainsString('deal.timeline.stage.moved', (string) $latestActivity->properties);
    }
}
