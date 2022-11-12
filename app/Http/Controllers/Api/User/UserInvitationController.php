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

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserInvitationRepository;

class UserInvitationController extends ApiController
{
    /**
     * Initialize new UserInvitationController instance.
     *
     * @param \App\Contracts\Repositories\UserInvitationRepository $repository
     */
    public function __construct(protected UserInvitationRepository $repository)
    {
    }

    /**
     * Invite the user to create account
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email', 'unique:' . (new User())->getTable()],
            'super_admin' => 'nullable|boolean',
            'access_api'  => 'nullable|boolean',
            'roles'       => 'nullable|array',
            'teams'       => 'nullable|array',
        ]);

        $this->repository->invite($data);

        return $this->response('', 204);
    }
}
