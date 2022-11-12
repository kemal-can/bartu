<?php

namespace Tests\Feature\Resource\Activity\Cards;

use Tests\Feature\Resource\ResourceTestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Resources\Activity\Cards\ActivitiesCreatedBySaleAgent;

class ActivitiesCreatedBySaleAgentTest extends ResourceTestCase
{
    protected $card;

    protected $resourceName = 'activities';

    protected function setUp() : void
    {
        parent::setUp();
        $this->card = new ActivitiesCreatedBySaleAgent;
    }

    protected function tearDown() : void
    {
        unset($this->card);
        parent::tearDown();
    }

    public function test_activities_created_by_sale_agent_card()
    {
        $this->signIn();

        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $this->factory()->for($user1, 'creator')->create();
        $this->factory()->for($user2, 'creator')->count(2)->create();

        $this->getJson("api/cards/{$this->card->uriKey()}")
            ->assertJson(
                fn (AssertableJson $json) => $json->has('result', 2)
                    ->has(
                        'result.0',
                        fn ($json) => $json->where('value', $json->toArray()['label'] === $user1->name ? 1 : 2)->etc()
                    )->has(
                        'result.1',
                        fn ($json) => $json->where('value', $json->toArray()['label'] === $user1->name ? 1 : 2)->etc()
                    )->etc()
            );
    }

    public function test_unauthorized_user_cannot_see_activities_created_by_sale_agent_card()
    {
        $this->asRegularUser()->signIn();

        $this->getJson("api/cards/{$this->card->uriKey()}")->assertForbidden();
    }
}
