<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Contracts\Repositories\UserInvitationRepository;

class UserInvitationRepositoryTest extends TestCase
{
    public function test_invitation_can_be_queried_by_token()
    {
        $this->signIn();

        $invitation = $this->newInstance();

        $this->assertEquals(
            $invitation->token,
            resolve(UserInvitationRepository::class)->findByToken($invitation->token)->token
        );
    }

    public function test_invitation_link_is_properly_generated()
    {
        $this->signIn();

        $invitation = $this->newInstance();

        $this->assertEquals($invitation->link, url("/invitation/{$invitation->token}"));
    }

    protected function newInstance($attributes = [])
    {
        return resolve(UserInvitationRepository::class)->create(array_merge([
            'email' => 'user@example.com',
        ], $attributes));
    }
}
