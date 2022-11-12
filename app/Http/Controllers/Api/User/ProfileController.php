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

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserRepository;

class ProfileController extends ApiController
{
    /**
     * Initialize new ProfileController instance.
     *
     * @param \App\Contracts\Repositories\UserRepository $repository
     */
    public function __construct(protected UserRepository $repository)
    {
    }

    /**
     * Get logged in user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        return $this->response(new UserResource(
            $this->repository->withResponseRelations()->find($request->user()->id)
        ));
    }

    /**
     * Update profile
     *
     * @param \App\Http\Requests\ProfileRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProfileRequest $request)
    {
        // Profile update flag
        $user = $this->repository->update(
            $request->except(['super_admin', 'access_api']),
            $request->user()->id
        );

        return $this->response(new UserResource($this->repository->withResponseRelations()->find($user->id)));
    }

    /**
     * Change password
     *
     * @param \App\Http\Requests\PasswordRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function password(PasswordRequest $request)
    {
        // Profile update password flag
        $this->repository->update(
            ['password' => $request->get('password')],
            $request->user()->id
        );

        return $this->response('', 204);
    }
}
