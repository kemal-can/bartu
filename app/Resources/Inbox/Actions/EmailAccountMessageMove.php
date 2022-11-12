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

use App\Innoclapps\Fields\Select;
use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Http\Resources\EmailAccountResource;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Contracts\Repositories\EmailAccountFolderRepository;
use App\Contracts\Repositories\EmailAccountMessageRepository;

class EmailAccountMessageMove extends Action
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

        $accountsRepository = resolve(EmailAccountRepository::class);
        $repository         = resolve(EmailAccountMessageRepository::class);

        $repository->batchMoveTo(
            $models,
            $fields->move_to_folder_id,
            request()->folder_id
        );

        return [
            'unread_count'       => $accountsRepository->countUnreadMessagesForUser(auth()->user()),
            'account'            => new EmailAccountResource($accountsRepository->withResponseRelations()->find($accountId)),
            'moved_to_folder_id' => $fields->move_to_folder_id,
        ];
    }

    /**
     * Get the action fields
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function fields(ResourceRequest $request) : array
    {
        return [
            Select::make('move_to_folder_id')
                ->labelKey('display_name')
                ->valueKey('id')
                ->rules('required')
                ->options(function () use ($request) {
                    return resolve(EmailAccountFolderRepository::class)
                        ->getForAccount((int) $request->account_id)
                        ->filter(function ($folder) {
                            return $folder->support_move;
                        });
                }),
        ];
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
        return __('inbox.move_to');
    }
}
