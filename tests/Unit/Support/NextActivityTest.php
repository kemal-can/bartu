<?php

namespace Tests\Unit\Support;

use Tests\TestCase;
use App\Models\Activity;
use App\Support\SyncNextActivity;
use App\Contracts\Repositories\ActivityRepository;
use Illuminate\Database\Eloquent\Factories\Sequence;

class NextActivityTest extends TestCase
{
    public function test_resource_has_next_activity()
    {
        $activities = $this->activityFactory()->create();

        foreach (SyncNextActivity::getResourcesWithNextActivity() as $resource) {
            $model = $resource->model()::factory()->create();
            $model->activities()->attach($activities);

            $this->invokeSync();
            $this->assertTrue($activities[0]->is($model->fresh()->nextActivity));

            $model->activities()->where('id', $activities[0]->id)->detach();
            $this->invokeSync();
            $this->assertTrue($activities[1]->is($model->fresh()->nextActivity));
        }
    }

    public function test_resource_next_activity_is_cleared_when_has_no_activities()
    {
        $activities = $this->activityFactory()->create();

        foreach (SyncNextActivity::getResourcesWithNextActivity() as $resource) {
            $model = $resource->model()::factory()->create();
            $model->activities()->attach($activities);
            $this->invokeSync();
            $model->activities()->detach();

            $this->invokeSync();
            $this->assertNull($model->fresh()->nextActivity);
        }
    }

    public function test_resource_next_activity_is_cleared_when_activities_are_completed()
    {
        $repository = app(ActivityRepository::class);

        foreach (SyncNextActivity::getResourcesWithNextActivity() as $resource) {
            $activities = $this->activityFactory()->create();
            $model      = $resource->model()::factory()->create();
            $model->activities()->attach($activities[0]);

            $repository->complete($activities[0]->id);
            $this->invokeSync();

            $this->assertNull($model->fresh()->nextActivity);
        }
    }

    protected function invokeSync()
    {
        (new SyncNextActivity())();
    }

    protected function activityFactory()
    {
        $now = now();

        return Activity::factory()->count(2)->state(new Sequence(
            ['due_date' => $now->addWeeks(1)->format('Y-m-d'), 'due_time' => $now->format('H:i:s')],
            ['due_date' => $now->addWeeks(2)->format('Y-m-d'), 'due_time' => $now->format('H:i:s')]
        ));
    }
}
