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
use App\Http\Requests\EmailAccountRequest;
use App\Http\Resources\EmailAccountResource;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Criteria\EmailAccount\EmailAccountsForUserCriteria;

class EmailAccountController extends ApiController
{
    /**
     * Initialize new EmailAccountController instance.
     *
     * @param \App\Contracts\Repositories\EmailAccountRepository $repository
     */
    public function __construct(protected EmailAccountRepository $repository)
    {
    }

    /**
     * Get all email accounts the user can access
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $accounts = $this->repository->withResponseRelations()
            ->pushCriteria(new EmailAccountsForUserCriteria($request->user()))
            ->all();

        return $this->response(
            EmailAccountResource::collection($accounts)
        );
    }

    /**
     * Display email account;
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $account = $this->repository->withResponseRelations()->find($id);

        $this->authorize('view', $account);

        return $this->response(new EmailAccountResource($account));
    }

    /**
     * Store a newly created email account in storage
     *
     * @param \App\Http\Requests\EmailAccountRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EmailAccountRequest $request)
    {
        $model = $this->repository->create($request->all());

        $account = $this->repository->withResponseRelations()->find($model->id);

        $account->wasRecentlyCreated = true;

        return $this->response(
            new EmailAccountResource($account),
            201
        );
    }

    /**
     * Update the specified account in storage
     *
     * @param int $id
     * @param \App\Http\Requests\EmailAccountRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, EmailAccountRequest $request)
    {
        $this->authorize('update', $this->repository->find($id));

        // The user is not allowed to update these fields after creation
        $except = ['email', 'connection_type', 'user_id', 'initial_sync_from'];

        $account = $this->repository->update($request->except($except), $id);

        return $this->response(
            new EmailAccountResource($this->repository->withResponseRelations()->find($account->id))
        );
    }

    /**
     * Remove the specified account from storage
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $this->authorize('delete', $this->repository->find($id));

        $this->repository->delete($id);

        return $this->response([
            'unread_count' => $this->repository->countUnreadMessagesForUser($request->user()),
        ]);
    }

    /**
     * Get all shared accounts unread messages
     *
     * @return \Illuminate\Http\Response
     */
    public function unread(Request $request)
    {
        return $this->repository->countUnreadMessagesForUser($request->user());
    }
}
