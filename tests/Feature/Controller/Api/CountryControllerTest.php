<?php

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;
use Database\Seeders\CountriesSeeder;

class CountryControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_country_endpoints()
    {
        $this->getJson('/api/countries')->assertUnauthorized();
    }

    public function test_user_can_fetch_countries()
    {
        $this->signIn();

        $this->seed(CountriesSeeder::class);

        $this->getJson('/api/countries')->assertOk();
    }
}
