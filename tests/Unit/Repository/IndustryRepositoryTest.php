<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Industry;
use App\Contracts\Repositories\IndustryRepository;

class IndustryRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(IndustryRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_indusry_with_companies_cannot_be_deleted()
    {
        $industry = Industry::factory()->has(Company::factory())->create();

        $this->expectExceptionMessage(__(
            'resource.associated_delete_warning',
            [
            'resource' => __('company.industry.industry'),
        ]
        ));

        $this->repository->delete($industry->id);
    }
}
