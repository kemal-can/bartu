<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Activity;
use App\Models\ActivityType;

class ActivityTypeTest extends TestCase
{
    public function test_activity_type_can_be_primary()
    {
        $type = ActivityType::factory()->primary()->create();

        $this->assertTrue($type->isPrimary());

        $type->flag = null;
        $type->save();

        $this->assertFalse($type->isPrimary());
    }

    public function test_activity_type_can_be_default()
    {
        $type = ActivityType::factory()->primary()->create();

        ActivityType::setDefault($type->id);

        $this->assertEquals($type->id, ActivityType::getDefaultType());
    }

    public function test_type_has_activities()
    {
        $type = ActivityType::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $type->activities);
    }
}
