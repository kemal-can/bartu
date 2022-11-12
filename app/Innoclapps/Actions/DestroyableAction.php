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

namespace App\Innoclapps\Actions;

use Illuminate\Support\Collection;

abstract class DestroyableAction extends Action
{
    /**
     * Provide the models repository class name
     *
     * @return string
     */
    abstract public function repository();

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
        $repository = resolve($this->repository());

        foreach ($models as $model) {
            $repository->delete($model->id);
        }
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
