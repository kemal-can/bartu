<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Models\Phone;
use App\Models\Company;
use App\Models\Contact;
use App\Enums\PhoneType;
use Database\Seeders\CountriesSeeder;
use App\Contracts\Repositories\ContactRepository;

class ContactRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(ContactRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_ensures_that_company_is_automatically_associated_to_contact_after_contact_creation_by_contact_email_domain()
    {
        Company::factory()->create(['domain' => 'bartucrm.com']);
        $contact = Contact::factory()->create(['email' => 'marjan@bartucrm.com']);

        $this->repository->associateCompaniesByEmailDomain($contact);

        $this->assertEquals(1, $contact->companies->count());
    }

    public function test_it_ensures_that_multiple_companies_can_be_automatically_associated_to_contact_by_email_domain()
    {
        Company::factory()->create(['domain' => 'bartucrm.com']);
        Company::factory()->create(['domain' => 'bartucrm.com']);

        $contact = Contact::factory()->create(['email' => 'marjan@bartucrm.com']);

        $this->repository->associateCompaniesByEmailDomain($contact);

        $this->assertEquals(2, $contact->companies->count());
    }

    public function test_it_does_not_associate_company_to_contact_by_email_domain_if_the_company_is_already_provided()
    {
        Company::factory()->create(['domain' => 'bartucrm.com']);
        Company::factory()->create(['domain' => 'bartucrm.test']);
        $contact = Contact::factory()->create(['email' => 'marjan@bartucrm.com']);

        $this->repository->associateCompaniesByEmailDomain($contact);

        $this->assertEquals(1, $contact->companies->count());
    }

    public function test_it_can_find_contact_by_phone()
    {
        $this->seed(CountriesSeeder::class);

        Contact::factory()->has(Phone::factory()->state(function ($attributes) {
            return ['number' => '255-255-255'];
        }))->create();

        $contact = $this->repository->findByPhone('255-255-255');

        $this->assertNotNull($contact);

        Contact::factory()->has(Phone::factory()->state(function ($attributes) {
            return ['number' => '255-255-244', 'type' => PhoneType::work];
        }))->create();

        $contact = $this->repository->findByPhone('255-255-244', PhoneType::work);

        $this->assertNotNull($contact);
    }

    public function test_it_can_find_contact_by_email()
    {
        Contact::factory()->create(['email' => 'marjan@bartucrm.com']);

        $contact = $this->repository->findByEmail('marjan@bartucrm.com');

        $this->assertNotNull($contact);
    }
}
