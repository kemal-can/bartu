<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Company;
use App\Contracts\Repositories\CompanyRepository;

class CompanyRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(CompanyRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_company_can_be_retrieved_by_domain()
    {
        $source = Company::factory()->create(['domain' => 'bartucrm.com']);

        $byDomain = $this->repository->findByDomain('bartucrm.com');

        $this->assertCount(1, $byDomain);
        $this->assertInstanceOf(Company::class, $byDomain[0]);
        $this->assertEquals($source->id, $byDomain[0]->id);
    }
}
