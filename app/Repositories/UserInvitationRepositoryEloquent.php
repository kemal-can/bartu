<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Repositories;

use App\Models\UserInvitation;
use App\Mail\InvitationCreated;
use Illuminate\Support\Facades\Mail;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\UserInvitationRepository;

class UserInvitationRepositoryEloquent extends AppRepository implements UserInvitationRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return UserInvitation::class;
    }

    /**
     * Find invitation by given token
     *
     * @param string $token
     *
     * @return \App\Models\UserInvitation|null
     */
    public function findByToken(string $token) : ?UserInvitation
    {
        return $this->findByField('token', $token)->first();
    }

    /**
     * Invite user
     *
     * @param array $data
     *
     * @return \App\Models\UserInvitation
     */
    public function invite(array $data) : UserInvitation
    {
        $invitation = $this->findByField('email', $data['email'])->first();

        if ($invitation) {
            $this->delete($invitation);
        }

        $invitation = $this->create([
            'email'       => $data['email'],
            'super_admin' => $data['super_admin'] ?? false,
            'access_api'  => $data['access_api'] ?? false,
            'roles'       => $data['roles'] ?? null,
            'teams'       => $data['teams'] ?? null,
        ]);

        Mail::to($invitation->email)->send(
            new InvitationCreated($invitation)
        );

        return $invitation;
    }
}
