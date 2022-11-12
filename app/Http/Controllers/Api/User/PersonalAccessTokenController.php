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
use App\Http\Controllers\ApiController;

class PersonalAccessTokenController extends ApiController
{
    /**
     * Get all user personal access tokens
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->response($request->user()->tokens);
    }

    /**
     * Create new user personal access token
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
        ]);

        return $request->user()->createToken(
            $request->name
        );
    }

    /**
     * Revoke the given user personal access token
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        abort_if(is_null($token), 404);

        $token->delete();

        return $this->response('', 204);
    }
}
