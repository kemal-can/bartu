<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Industry;

class IndustryTest extends TestCase
{
    public function test_industry_has_companies()
    {
        $industry = Industry::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $industry->companies);
    }
}
