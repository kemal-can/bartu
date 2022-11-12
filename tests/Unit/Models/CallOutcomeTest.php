<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Call;
use App\Models\CallOutcome;

class CallOutcomeTest extends TestCase
{
    public function test_outcome_has_calls()
    {
        $outcome = CallOutcome::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $outcome->calls);
    }
}
