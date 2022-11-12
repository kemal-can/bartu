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
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class IssueTokenController extends ApiController
{
    /**
     * Exchange Token
     *
     * Exchange new token for a given valid username and password
     *
     * The endpoint will return the plain-text token which may then be stored on the mobile device or other storage
     * and used to make additional API requests.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\UserRepository $repossitory
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UserRepository $repository)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:191',
        ]);

        $user = $repository->findByEmail($request->email);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->response([
            'accessToken' => $user->createToken($request->device_name)->plainTextToken,
            'userId'      => $user->id,
            'email'       => $user->email,
            'name'        => $user->name,
        ]);
    }
}
