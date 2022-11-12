<?php

namespace Tests\Unit\Innoclapps\Macros\Arr;

use Tests\TestCase;
use Illuminate\Support\Arr;

class ToObjectTest extends TestCase
{
    public function test_it_converts_array_to_object()
    {
        $object = Arr::toObject(['key' => 'value', 'children' => ['key' => 'value']]);

        $this->assertIsObject($object);
        $this->assertObjectHasAttribute('key', $object);
        $this->assertSame('value', $object->key);
        $this->assertObjectHasAttribute('children', $object);
        $this->assertSame('value', $object->children->key);
    }

    public function test_it_returns_empty_object_when_the_provided_value_is_not_an_array()
    {
        $object = Arr::toObject(null);

        $this->assertIsObject($object);
    }
}
