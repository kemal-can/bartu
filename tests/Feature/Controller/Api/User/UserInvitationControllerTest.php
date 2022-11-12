<?php

namespace Tests\Feature\Controller\Api\User;

use Tests\TestCase;
use App\Models\Team;
use App\Mail\InvitationCreated;
use Illuminate\Support\Facades\Mail;
use App\Contracts\Repositories\UserInvitationRepository;

class UserInvitationControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_invitation_endpoints()
    {
        $this->postJson('/api/users/invite')->assertUnauthorized();
    }

    public function test_invitation_cannot_be_sent_by_non_authorized_user()
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/users/invite')->assertForbidden();
    }

    public function test_invitation_can_be_sent_by_authorized_user()
    {
        $this->signIn();

        Mail::fake();

        $role = $this->createRole();
        $team = Team::factory()->create();

        $this->postJson('/api/users/invite', [
            'email'       => $email = 'user@example.com',
            'super_admin' => 1,
            'access_api'  => 1,
            'roles'       => $roles = [$role->id],
            'teams'       => $teams = [$team->id],
        ]);

        Mail::assertQueued(InvitationCreated::class, 1);

        $this->assertDatabaseHas('user_invitations', [
            'email'       => $email,
            'super_admin' => 1,
            'access_api'  => 1,
            'roles'       => json_encode($roles),
            'teams'       => json_encode($teams),
        ]);
    }

    public function test_invitation_requires_email()
    {
        $this->signIn();

        $this->postJson('/api/users/invite', [
            'email' => null,
        ])->assertJsonValidationErrors('email');

        $this->postJson('/api/users/invite', [
            'email' => 'dummy',
        ])->assertJsonValidationErrors('email'); // invalid email
    }

    public function test_cannot_invite_user_that_already_exist()
    {
        $user = $this->signIn();

        $this->postJson('/api/users/invite', [
            'email' => $user->email,
        ])->assertJsonValidationErrors(['email' => __('validation.unique', ['attribute' => 'email'])]);
    }

    public function test_previous_invitation_is_deleted_when_inviting_the_same_user()
    {
        $this->signIn();

        $invitation = $this->newInstance();

        $this->postJson('/api/users/invite', [
          'email' => 'user@example.com',
        ]);

        $this->assertDatabaseMissing('user_invitations', [
            $invitation->getKeyName() => $invitation->id,
        ]);
    }

    protected function newInstance($attributes = [])
    {
        return resolve(UserInvitationRepository::class)->create(array_merge([
            'email' => 'user@example.com',
        ], $attributes));
    }
}
