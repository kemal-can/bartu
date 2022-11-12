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

use App\Enums\SyncState;
use App\Http\Controllers\ApiController;
use App\Http\Resources\EmailAccountResource;
use App\Contracts\Repositories\EmailAccountRepository;

class EmailAccountSyncStateController extends ApiController
{
    /**
     * Initialize new EmailAccountSyncStateController instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Enable synchronization for email account
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable($id)
    {
        $account = $this->repository->find($id);

        $this->authorize('update', $account);

        if ($account->isSyncStoppedBySystem()) {
            abort(403, 'Synchronization for this account is stopped by system. [' . $account->sync_state_comment . ']');
        }

        $this->repository->enableSync($account->id);

        return $this->response(
            new EmailAccountResource($this->repository->withResponseRelations()->find($id))
        );
    }

    /**
     * Enable synchronization for email account
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable($id)
    {
        $account = $this->repository->find($id);

        $this->authorize('update', $account);

        $this->repository->setSyncState(
            $account->id,
            SyncState::DISABLED,
            'Account synchronization disabled by user.'
        );

        return $this->response(
            new EmailAccountResource($this->repository->withResponseRelations()->find($id))
        );
    }
}
