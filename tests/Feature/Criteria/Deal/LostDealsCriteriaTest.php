<?php

namespace Tests\Feature\Criteria\Deal;

use Tests\TestCase;
use App\Models\Deal;
use App\Criteria\Deal\LostDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class LostDealsCriteriaTest extends TestCase
{
    public function test_lost_deals_criteria()
    {
        $repository = app(DealRepository::class);
        $repository->pushCriteria(LostDealsCriteria::class);
        Deal::factory()->won()->create();
        Deal::factory()->lost()->create();
        Deal::factory()->open()->create();

        $this->assertCount(1, $repository->all());
    }
}
