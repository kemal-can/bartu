<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Company;
use App\Models\Contact;

class SourceTest extends TestCase
{
    public function test_source_can_be_primary()
    {
        $source = Source::factory()->primary()->create();
        $this->assertTrue($source->isPrimary());

        $source->flag = null;
        $source->save();

        $this->assertFalse($source->isPrimary());
    }

    public function test_source_has_contacts()
    {
        $source = Source::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $source->contacts);
    }

    public function test_source_has_companies()
    {
        $source = Source::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $source->companies);
    }
}
