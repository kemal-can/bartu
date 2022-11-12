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

class ContactTest extends TestCase
{
    public function test_when_contact_created_by_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $contact = Contact::factory(['created_by' => null])->create();

        $this->assertEquals($contact->created_by, $user->id);
    }

    public function test_contact_created_by_can_be_provided()
    {
        $user = $this->createUser();

        $contact = Contact::factory()->for($user, 'creator')->create();

        $this->assertEquals($contact->created_by, $user->id);
    }

    public function test_contact_has_path_attribute()
    {
        $contact = Contact::factory()->create();

        $this->assertEquals('/contacts/1', $contact->path);
    }

    public function test_contact_has_display_name_attribute()
    {
        $contact = Contact::factory(['first_name' => 'Firstname', 'last_name' => 'Lastname'])->make();

        $this->assertEquals('Firstname Lastname', $contact->display_name);
    }

    public function test_contact_has_country()
    {
        $this->seed(CountriesSeeder::class);

        $contact = Contact::factory()->for(Country::first())->create();

        $this->assertInstanceOf(Country::class, $contact->country);
    }

    public function test_contact_has_user()
    {
        $contact = Contact::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $contact->user);
    }

    public function test_contact_has_source()
    {
        $contact = Contact::factory()->for(Source::factory())->create();

        $this->assertInstanceOf(Source::class, $contact->source);
    }

    public function test_contact_has_deals()
    {
        $contact = Contact::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $contact->deals);
    }

    public function test_contact_has_phones()
    {
        $this->seed(CountriesSeeder::class);

        $contact = Contact::factory()->has(Phone::factory()->count(2))->create();

        $this->assertCount(2, $contact->phones);
    }

    public function test_contact_has_calls()
    {
        $contact = Contact::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $contact->calls);
    }

    public function test_contact_has_notes()
    {
        $contact = Contact::factory()->has(Note::factory()->count(2))->create();

        $this->assertCount(2, $contact->notes);
    }

    public function test_contact_has_companies()
    {
        $contact = Contact::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $contact->companies);
    }

    public function test_contact_has_activities()
    {
        $contact = Contact::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $contact->activities);
    }
}
