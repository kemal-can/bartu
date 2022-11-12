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
use App\Http\Resources\EmailAccountResource;
use App\Contracts\Repositories\EmailAccountRepository;

class SharedEmailAccountController extends ApiController
{
    /**
     * Initialize new SharedEmailAccountController instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Display shared email accounts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        $accounts = $this->repository->withResponseRelations()->getShared();

        return $this->response(
            EmailAccountResource::collection($accounts)
        );
    }
}
