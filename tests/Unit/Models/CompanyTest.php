<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Call;
use App\Models\Deal;
use App\Models\Note;
use App\Models\User;
use App\Models\Phone;
use App\Models\Source;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Activity;
use App\Innoclapps\Models\Country;
use Database\Seeders\CountriesSeeder;

class CompanyTest extends TestCase
{
    public function test_when_company_created_by_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $company = Company::factory(['created_by' => null])->create();

        $this->assertEquals($company->created_by, $user->id);
    }

    public function test_company_created_by_can_be_provided()
    {
        $user = $this->createUser();

        $company = Company::factory()->for($user, 'creator')->create();

        $this->assertEquals($company->created_by, $user->id);
    }

    public function test_company_has_path_attribute()
    {
        $company = Company::factory()->create();

        $this->assertEquals('/companies/1', $company->path);
    }

    public function test_company_has_display_name_attribute()
    {
        $company = Company::factory(['name' => 'Company name'])->make();

        $this->assertEquals('Company name', $company->display_name);
    }

    public function test_company_has_country()
    {
        $this->seed(CountriesSeeder::class);

        $company = Company::factory()->for(Country::first())->create();

        $this->assertInstanceOf(Country::class, $company->country);
    }

    public function test_company_has_user()
    {
        $company = Company::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $company->user);
    }

    public function test_company_has_source()
    {
        $company = Company::factory()->for(Source::factory())->create();

        $this->assertInstanceOf(Source::class, $company->source);
    }

    public function test_company_has_deals()
    {
        $company = Company::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $company->deals);
    }

    public function test_company_has_phones()
    {
        $this->seed(CountriesSeeder::class);

        $company = Company::factory()->has(Phone::factory()->count(2))->create();

        $this->assertCount(2, $company->phones);
    }

    public function test_company_has_calls()
    {
        $company = Company::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $company->calls);
    }

    public function test_company_has_notes()
    {
        $company = Company::factory()->has(Note::factory()->count(2))->create();

        $this->assertCount(2, $company->notes);
    }

    public function test_company_has_contacts()
    {
        $company = Company::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $company->contacts);
    }

    public function test_company_has_activities()
    {
        $company = Company::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $company->activities);
    }
}
