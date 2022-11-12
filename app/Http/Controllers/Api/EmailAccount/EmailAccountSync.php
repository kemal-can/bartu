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
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\EmailAccountResource;
use App\Contracts\Repositories\EmailAccountRepository;

class EmailAccountSync extends ApiController
{
    /**
     * Initialize new EmailAccountSync instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Synchronize email account
     *
     * @param int $accountId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \App\MailClient\Exceptions\SynchronizationInProgressException
     */
    public function __invoke($accountId)
    {
        $this->authorize('view', $this->repository->find($accountId));

        Artisan::call('bartu:sync-email-accounts', [
            '--account'   => $accountId,
            '--broadcast' => false,
            '--manual'    => true,
        ]);

        return $this->response(
            new EmailAccountResource(
                $this->repository->withResponseRelations()->find($accountId)
            )
        );
    }
}
