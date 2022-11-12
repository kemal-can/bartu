<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Company;
use App\Models\Contact;
use App\Contracts\Repositories\SourceRepository;

class SourceRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(SourceRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_source_can_be_retrieved_by_flag()
    {
        $source = Source::factory()->create(['flag' => 'custom-flag']);

        $byFlag = $this->repository->findByFlag('custom-flag');

        $this->assertInstanceOf(Source::class, $byFlag);
        $this->assertEquals($source->id, $byFlag->id);
    }

    public function test_primary_source_cannot_be_deleted()
    {
        $source = Source::factory()->primary()->create();
        $this->expectExceptionMessage(__('source.delete_primary_warning'));

        $this->repository->delete($source->id);
    }

    public function test_source_with_contacts_cannot_be_deleted()
    {
        $source = Source::factory()->has(Contact::factory()->for($this->createUser()))->create();

        $this->expectExceptionMessage(__(
            'resource.associated_delete_warning',
            [
            'resource' => __('sources.source'),
        ]
        ));

        $this->repository->delete($source->id);
    }

    public function test_source_with_companies_cannot_be_deleted()
    {
        $source = Source::factory()->has(Company::factory())->create();

        $this->expectExceptionMessage(__(
            'resource.associated_delete_warning',
            [
            'resource' => __('sources.source'),
        ]
        ));

        $this->repository->delete($source->id);
    }
}
