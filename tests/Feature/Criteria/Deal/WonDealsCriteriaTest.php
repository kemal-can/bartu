<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Criteria\Deal\WonDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class WonDealsCriteriaTest extends TestCase
{
    public function test_won_deals_criteria()
    {
        $repository = app(DealRepository::class);
        $repository->pushCriteria(WonDealsCriteria::class);
        Deal::factory()->won()->create();
        Deal::factory()->lost()->create();
        Deal::factory()->open()->create();

        $this->assertCount(1, $repository->all());
    }
}
