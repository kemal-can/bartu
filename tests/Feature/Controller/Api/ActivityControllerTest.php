<?php

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;
use App\Models\Activity;

class ActivityControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_activity_end_points()
    {
        $this->getJson('/api/activities/FAKE_ID/ics')->assertUnauthorized();
    }

    public function test_activity_ics_file_can_be_downloaded()
    {
        $this->signIn();

        $activity = Activity::factory()->create();

        $this->getJson('/api/activities/' . $activity->getKey() . '/ics')
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename=' . $activity->icsFilename() . '.ics');
    }
}
