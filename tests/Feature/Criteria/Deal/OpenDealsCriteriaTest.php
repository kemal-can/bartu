<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Criteria\Deal\OpenDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class OpenDealsCriteriaTest extends TestCase
{
    public function test_open_deals_criteria()
    {
        $repository = app(DealRepository::class);
        $repository->pushCriteria(OpenDealsCriteria::class);
        Deal::factory()->won()->create();
        Deal::factory()->lost()->create();
        Deal::factory()->open()->create();

        $this->assertCount(1, $repository->all());
    }
}
