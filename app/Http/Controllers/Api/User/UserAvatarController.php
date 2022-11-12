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
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserRepository;

class UserAvatarController extends ApiController
{
    /**
     * Upload user avatar
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Http\Requests\UserRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UserRepository $repository)
    {
        $request->validate([
            'avatar' => 'required|image|max:1024',
        ]);

        $user = $repository->storeAvatar($request->user(), $request->file('avatar'));

        return $this->response(new UserResource($repository->withResponseRelations()->find($user->id)));
    }

    /**
     * Delete the user avatar
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\UserRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, UserRepository $repository)
    {
        $user = $repository->removeAvatarImage($request->user())
            ->update(['avatar' => null], $request->user()->id);

        return $this->response(new UserResource($repository->withResponseRelations()->find($user->id)));
    }
}
