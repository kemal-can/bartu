<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Contracts\Repositories\ActivityTypeRepository;

class ActivityTypeRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(ActivityTypeRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_primary_type_cannot_be_deleted()
    {
        $type = ActivityType::factory()->primary()->create();
        $this->expectExceptionMessage(__('activity.type.delete_primary_warning'));

        $this->repository->delete($type->id);
    }

    public function test_default_type_cannot_be_deleted()
    {
        $type = ActivityType::factory()->create();
        ActivityType::setDefault($type->id);
        $this->expectExceptionMessage(__('activity.type.delete_is_default'));

        $this->repository->delete($type->id);
    }

    public function test_type_with_activities_cannot_be_deleted()
    {
        $type = ActivityType::factory()->has(Activity::factory())->create();

        $this->expectExceptionMessage(__('activity.type.delete_usage_warning'));

        $this->repository->delete($type->id);
    }
}
