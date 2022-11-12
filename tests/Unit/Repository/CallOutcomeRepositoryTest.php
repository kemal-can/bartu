<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Contracts\Repositories\CallOutcomeRepository;

class CallOutcomeRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(CallOutcomeRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_outcome_with_calls_cannot_be_deleted()
    {
        $outcome = CallOutcome::factory()->has(Call::factory())->create();

        $this->expectExceptionMessage(__('call.outcome.delete_warning'));

        $this->repository->delete($outcome->id);
    }
}
