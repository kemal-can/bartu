<?php

namespace Tests\Unit\Innoclapps\Criteria;

use Tests\TestCase;
use App\Models\Source;
use App\Models\Contact;
use Illuminate\Support\Facades\Request;
use App\Innoclapps\Criteria\RequestCriteria;
use App\Contracts\Repositories\ContactRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Innoclapps\Criteria\SearchByFirstNameAndLastNameCriteria;

class RequestCriteriaTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
        $this->searchFields = app(ContactRepository::class)->getFieldsSearchable();
    }

    protected function tearDown() : void
    {
        app(ContactRepository::class)->setSearchableFields($this->searchFields);
        unset($this->searchFields);
        parent::tearDown();
    }

    protected function createCriteriaRequest($params)
    {
        $query = (is_array($params) ? http_build_query($params) : $params);

        return Request::create('/fake?' . $query, 'GET');
    }

    protected function repository($params)
    {
        return app(ContactRepository::class)->pushCriteria(new RequestCriteria(
            $this->createCriteriaRequest($params),
        ));
    }

    public function test_it_aborts_when_all_provided_fields_are_not_searchable()
    {
        Contact::factory()->create(['first_name' => 'John', 'email' => 'email@example.com']);

        $repository = $this->repository('q=John&search_fields=email')->setSearchableFields(['first_name' => '=']);

        try {
            $repository->get();
        } catch (\Throwable $e) {
        }

        $this->assertEquals(
            new HttpException(403, 'None of the search fields were accepted. Acceptable search fields are: first_name'),
            $e
        );
    }

    public function test_it_accepts_search_fields_in_query_parameter()
    {
        Contact::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Contact::factory()->create(['last_name' => 'Johne', 'first_name' => 'Test']);

        $repository = $this->repository('q=first_name:John;last_name:John')
            ->setSearchableFields(['first_name' => '=', 'last_name' => 'like']);

        $this->assertCount(2, $repository->get());

        Contact::factory()->create(['first_name' => 'unique', 'last_name' => 'Doe']);

        $repository = $this->repository('q=first_name:John;last_name:Doe&search_fields=first_name:like;last_name:=')
            ->setSearchableFields(['first_name' => 'like', 'last_name' => '=']);

        $this->assertCount(2, $repository->get());
    }

    public function test_it_accepts_multiple_searchable_fields()
    {
        Contact::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Contact::factory()->create(['last_name' => 'Dan', 'last_name' => 'Johne']);

        $repository = $this->repository('q=John&search_fields=first_name:=;last_name:like')
            ->setSearchableFields(['first_name' => '=', 'last_name' => 'like']);

        $this->assertCount(2, $repository->get());
    }

    public function test_it_can_specify_the_search_match()
    {
        Contact::factory()->create(['first_name' => 'Unique', 'last_name' => 'Doe']);
        Contact::factory()->create(['first_name' => 'Johne', 'last_name' => 'Doe']);

        $repository = $this->repository(
            'q=first_name:John;last_name:Doe&search_fields=first_name:like;last_name:=&search_match=and'
        )->setSearchableFields(['first_name' => 'like', 'last_name' => '=']);

        $this->assertCount(1, $repository->get());
    }

    public function test_it_accept_searchable_fields_with_like_operator()
    {
        Contact::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Contact::factory()->create(['last_name' => 'Dan', 'last_name' => 'John']);

        $repository = $this->repository('q=Joh&search_fields=first_name:like')
            ->setSearchableFields(['first_name' => 'like', 'last_name' => 'like']);

        $this->assertCount(1, $repository->get());
    }

    public function test_it_uses_only_the_allowed_search_fields()
    {
        Contact::factory()->create(['first_name' => 'Same']);
        Contact::factory()->create(['last_name' => 'Same']);
        $repository = $this->repository('q=Same&search_fields=email;first_name')
            ->setSearchableFields(['first_name' => '=']);

        $this->assertCount(1, $repository->get());

        $repository = $this->repository('q=first_name:Same;last_name:Same')
            ->setSearchableFields(['first_name' => '=']);

        $this->assertCount(1, $repository->get());
    }

    public function test_when_no_searchable_fields_provided_it_uses_the_defined_ones()
    {
        Contact::factory()->create(['first_name' => 'John']);

        $repository = $this->repository('q=John')->setSearchableFields(['first_name' => '=']);

        $this->assertCount(1, $repository->get());
    }

    public function test_it_accept_searchable_fields_with_equal_operator()
    {
        Contact::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Contact::factory()->create(['last_name' => 'Dan', 'last_name' => 'John']);

        $repository = $this->repository('q=John&search_fields=first_name:=')
            ->setSearchableFields(['first_name' => 'like', 'last_name' => 'like']);


        $this->assertCount(1, $repository->get());
    }

    public function test_it_appends_to_request_criteria()
    {
        Contact::factory()->create(['first_name' => 'full', 'last_name' => 'last']);

        $repository = $this->repository('q=full name')
            ->appendToRequestCriteria(new SearchByFirstNameAndLastNameCriteria);

        $this->assertCount(1, $repository->get());
    }

    public function test_it_can_take_specified_number()
    {
        Contact::factory()->count(3)->create();

        $results = $this->repository('take=2')->get();

        $this->assertCount(2, $results);
    }

    public function test_can_eager_load_relations()
    {
        Contact::factory()->create();

        $results = $this->repository('with=source;user')->get();

        $this->assertTrue($results[0]->relationLoaded('source'));
        $this->assertTrue($results[0]->relationLoaded('user'));

        // Single
        $results = $this->repository('with=source')->get();

        $this->assertTrue($results[0]->relationLoaded('source'));

        // array
        $results = $this->repository(['with' => ['source', 'user']])->get();

        $this->assertTrue($results[0]->relationLoaded('source'));
        $this->assertTrue($results[0]->relationLoaded('user'));
    }

    public function test_it_selects_only_the_provided_columns()
    {
        Contact::factory()->create();

        $results = $this->repository('select=id;first_name;email')->get();

        $this->assertNull($results[0]->last_name);
        $this->assertNotNull($results[0]->id);
        $this->assertNotNull($results[0]->first_name);
        $this->assertNotNull($results[0]->email);

        // array
        $results = $this->repository(['select' => ['id', 'first_name', 'email']])->get();

        $this->assertNull($results[0]->last_name);
        $this->assertNotNull($results[0]->id);
        $this->assertNotNull($results[0]->first_name);
        $this->assertNotNull($results[0]->email);
    }

    public function test_it_applies_search_and_where_when_relation()
    {
        Contact::factory()->for(Source::factory([
            'name' => 'Source Name',
        ]))->create();

        $repository = $this->repository('q=source.name:Source Name&search_match=and')
            ->setSearchableFields(['source.name' => '=']);

        $this->assertCount(1, $repository->get());
    }

    public function test_it_applies_search_or_where_when_relation()
    {
        Contact::factory()->for(Source::factory([
            'name' => 'Source Name',
        ]))->create();
        Contact::factory()->create(['first_name' => 'John']);

        $repository = $this->repository('q=first_name:John;source.name:Source Name&search_match=or')
            ->setSearchableFields(['source.name' => '=', 'first_name' => '=']);

        $this->assertCount(2, $repository->get());
    }

    public function test_it_applies_order_when_table_is_provided()
    {
        $contact = Contact::factory()->for(Source::factory(['created_at' => now()->addDay(4)]))->create();
        Contact::factory()->create(['created_at' => now()->addDay(5)]);

        // With providing the table
        $results = $this->repository(['order' => ['field' => 'sources|created_at', 'direction' => 'desc']])->get();
        $this->assertEquals($contact->id, $results[0]->id);

        // With custom foreign key name
        $results = $this->repository(['order' => ['field' => 'sources:source_id|created_at', 'direction' => 'desc']])->get();
        $this->assertEquals($contact->id, $results[0]->id);
    }

    public function it_applies_order_on_multiple_provided_fields()
    {
        $contact1 = Contact::factory()->create(['first_name' => 'B', 'created_at' => now()->subDay(3)]);
        $contact2 = Contact::factory()->create(['created_at' => now()->subDay(5), 'first_name' => 'C']);
        $contact3 = Contact::factory()->create(['created_at' => now()->subDay(4), 'first_name' => 'A']);

        $results = $this->repository(['order' => [
            ['field' => 'created_at', 'direction' => 'asc'],
            ['field' => 'first_name', 'direction' => 'desc'],
        ]])->get();

        $this->assertEquals($contact2->id, $results[0]->id);
        $this->assertEquals($contact3->id, $results[1]->id);
        $this->assertEquals($contact1->id, $results[2]->id);
    }

    public function test_it_applies_the_provided_order()
    {
        $contact = Contact::factory()->create(['created_at' => now()->addDay(1)]);
        Contact::factory()->create(['created_at' => now()->subDay(5)]);

        $results = $this->repository('order=created_at|desc')->get();

        $this->assertEquals($contact->id, $results[0]->id);

        $results = $this->repository('order=created_at|asc')->get();
        $this->assertEquals($contact->id, $results[1]->id);

        // default asc
        $results = $this->repository('order=created_at')->get();
        $this->assertEquals($contact->id, $results[1]->id);

        $results = $this->repository(['order' => ['field' => 'created_at', 'direction' => 'asc']])->get();
        $this->assertEquals($contact->id, $results[1]->id);

        $results = $this->repository(['order' => ['field' => 'created_at', 'direction' => 'asc']])->get();
        $this->assertEquals($contact->id, $results[1]->id);

        // default asc
        $results = $this->repository(['order' => ['field' => 'created_at', 'direction' => '']])->get();
        $this->assertEquals($contact->id, $results[1]->id);
    }
}
