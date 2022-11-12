<?php

namespace Tests\Unit\Innoclapps\Macros\Arr;

use Tests\TestCase;
use Illuminate\Support\Arr;

class CastValuesAsStringTest extends TestCase
{
    public function test_it_casts_values_as_string()
    {
        $casts = Arr::valuesAsString([1, 2, 3]);

        $this->assertSame('1', $casts[0]);
        $this->assertSame('2', $casts[1]);
        $this->assertSame('3', $casts[2]);
    }
}
