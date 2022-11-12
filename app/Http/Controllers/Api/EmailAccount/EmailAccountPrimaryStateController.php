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

namespace App\Http\Controllers\Api\EmailAccount;

use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\EmailAccountRepository;

class EmailAccountPrimaryStateController extends ApiController
{
    /**
     * Initialize new EmailAccountPrimaryStateController instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Mark the given account as primary
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $this->authorize('view', $account = $this->repository->find($id));

        $this->repository->markAsPrimary($account, auth()->user());

        return $this->response('', 204);
    }

    /**
     * Remove primary account
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $this->repository->removePrimary(auth()->user());

        return $this->response('', 204);
    }
}
