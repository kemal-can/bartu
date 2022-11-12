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

namespace App\Resources\User\Actions;

use App\Innoclapps\Fields\User;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Innoclapps\Actions\DestroyableAction;
use App\Contracts\Repositories\UserRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class UserDelete extends DestroyableAction
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
        // User delete action flag
        $repository = resolve($this->repository());

        foreach ($models as $model) {
            $repository->delete($model->id, (int) $fields->user_id);
        }
    }

    /**
     * Provide the models repository class name
     *
     * @return string
     */
    public function repository()
    {
        return UserRepository::class;
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
            User::make('')
                ->help(__('user.transfer_data_info'))
                ->helpDisplay('text')
                ->rules('required'),
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
        return $request->user()->isSuperAdmin();
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('user.actions.delete');
    }
}
