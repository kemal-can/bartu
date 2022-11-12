<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Criteria\Deal\ClosedDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class ClosedDealsCriteriaTest extends TestCase
{
    public function test_closed_deals_criteria()
    {
        $repository = app(DealRepository::class);
        $repository->pushCriteria(ClosedDealsCriteria::class);
        Deal::factory()->won()->create();
        Deal::factory()->lost()->create();
        Deal::factory()->open()->create();

        $this->assertCount(2, $repository->all());
    }
}
