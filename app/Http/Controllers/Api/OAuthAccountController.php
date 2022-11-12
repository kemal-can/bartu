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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Support\PurgesOAuthAccount;
use App\Http\Controllers\ApiController;
use App\Http\Resources\OAuthAccountResource;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class OAuthAccountController extends ApiController
{
    use PurgesOAuthAccount;

    /**
     * OAuthAccountController constructor.
     *
     * @param \App\Innoclapps\Contracts\Repositories\OAuthAccountRepository $repository
     */
    public function __construct(protected OAuthAccountRepository $repository)
    {
    }

    /**
     * Get the user connected OAuth accounts
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->response(
            OAuthAccountResource::collection(
                $this->repository->findByField('user_id', $request->user()->id)->all()
            )
        );
    }

    /**
     * Display the specified oauth account.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $account = $this->repository->find($id);

        $this->authorize('view', $account);

        return $this->response(new OAuthAccountResource($account));
    }

    /**
     * Remove the specified account from storage
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', $account = $this->repository->find($id));

        $this->purgeOAuthAccount($account);

        return $this->response('', 204);
    }
}
