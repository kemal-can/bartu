<?php

namespace Tests\Feature\Controller\Api\Resource;

use Tests\TestCase;
use App\Models\Contact;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Resources\Contact\Contact as ContactResource;

class SearchControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_resource_search_endpoints()
    {
        $this->json('GET', '/api/contacts/search')->assertUnauthorized();
    }

    public function test_non_searchable_resource_cannot_be_searched()
    {
        $this->signIn();

        $searchableFields = ContactResource::repository()->getFieldsSearchable();

        ContactResource::repository()->setSearchableFields([]);

        $this->json('GET', '/api/contacts/search?q=test')
            ->assertNotFound();

        ContactResource::repository()->setSearchableFields($searchableFields);
    }

    public function test_own_criteria_is_applied_on_resource_search()
    {
        $this->seed(PermissionsSeeder::class);

        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->signIn();

        Contact::factory()->count(2)->state(new Sequence(
            ['first_name' => 'John', 'user_id' => $user->getKey()],
            ['first_name' => 'John', 'user_id' => null]
        ))->create();

        $this->getJson('/api/contacts/search?q=john')
            ->assertJsonCount(1);
    }

    public function test_it_does_not_return_any_results_if_search_query_is_empty()
    {
        $this->signIn();

        Contact::factory()->create();

        $this->json('GET', '/api/contacts/search?q=')
            ->assertJsonCount(0);
    }
}
