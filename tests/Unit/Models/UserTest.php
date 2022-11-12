<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Activity;
use App\Models\PredefinedMailTemplate;

class UserTest extends TestCase
{
    public function test_user_has_companies()
    {
        $user = User::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $user->companies);
    }

    public function test_user_has_contacts()
    {
        $user = User::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $user->contacts);
    }

    public function test_user_has_deals()
    {
        $user = User::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $user->deals);
    }

    public function test_user_has_activities()
    {
        $user = User::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $user->activities);
    }

    public function test_user_has_predefined_mail_templates()
    {
        $user = User::factory()->has(PredefinedMailTemplate::factory()->count(2))->create();

        $this->assertCount(2, $user->predefinedMailTemplates);
    }
}
