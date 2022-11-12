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

namespace App\Resources\Inbox\Actions;

use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Http\Resources\EmailAccountResource;
use App\Innoclapps\Actions\DestroyableAction;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Contracts\Repositories\EmailAccountMessageRepository;

class EmailAccountMessageDelete extends DestroyableAction
{
    /**
     * Handle method
     *
     * @param \Illuminate\Support\Collection $models
     * @param \App\Innoclapps\Actions\ActionFields $fields
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        $accountId = request()->account_id;

        $repository        = app($this->repository());
        $accountRepository = app(EmailAccountRepository::class);
        $repository->deleteForAccount($models, request()->folder_id);

        $account = $accountRepository->withResponseRelations()->find($accountId);

        return [
            'unread_count'    => $accountRepository->countUnreadMessagesForUser(auth()->user()),
            'account'         => new EmailAccountResource($account),
            'trash_folder_id' => $account->trashFolder->id,
        ];
    }

    /**
     * Provide the models repository class name
     *
     * @return string
     */
    public function repository()
    {
        return EmailAccountMessageRepository::class;
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->can('view', $model->account);
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('app.delete');
    }
}
