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

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\EmailAccountResource;
use App\Contracts\Repositories\EmailAccountRepository;

class PersonalEmailAccountController extends ApiController
{
    /**
     * Initialize new PersonalEmailAccountController instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Display personal email accounts for the logged in user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $accounts = $this->repository->withResponseRelations()->getPersonal($request->user()->id);

        return $this->response(
            EmailAccountResource::collection($accounts)
        );
    }
}
