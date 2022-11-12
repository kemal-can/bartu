<?php

namespace Tests\Unit\Innoclapps\Mail\Headers;

use Tests\TestCase;
use App\Innoclapps\Mail\Headers\Header;
use Illuminate\Contracts\Support\Arrayable;

class HeaderTest extends TestCase
{
    public function test_header_has_name()
    {
        $header = new Header('x-bartu-test', 'value');

        $this->assertSame('x-bartu-test', $header->getName());
    }

    public function test_header_name_is_aways_in_lowercase()
    {
        $header = new Header('X-bartu-Value', 'value');

        $this->assertSame('x-bartu-value', $header->getName());
    }

    public function test_header_has_value()
    {
        $header = new Header('x-bartu-test', 'value');

        $this->assertSame('value', $header->getValue());
    }

    public function test_header_is_arrayable()
    {
        $header = new Header('x-bartu-test', 'value');

        $this->assertInstanceOf(Arrayable::class, $header);

        $this->assertEquals([
            'name'  => 'x-bartu-test',
            'value' => 'value',
        ], $header->toArray());
    }
}
