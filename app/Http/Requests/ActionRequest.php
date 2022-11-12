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

namespace App\Http\Requests;

use App\Innoclapps\Table\Table;
use App\Innoclapps\Actions\Action;
use App\Innoclapps\Actions\ActionFields;
use App\Innoclapps\Resources\Http\ResourceRequest;

class ActionRequest extends ResourceRequest
{
    /**
     * Get the action for the request
     *
     * @return \App\Innoclapps\Actions\Action
     */
    public function action() : Action
    {
        return once(function () {
            return $this->availableActions()->first(function ($action) {
                return $action->uriKey() == $this->route('action');
            }) ?: abort($this->actionExists() ? 403 : 404);
        });
    }

    /**
     * Run the action for the current request
     *
     * @return mixed
     */
    public function run()
    {
        return $this->action()->run($this, $this->resource()->repository());
    }

    /**
     * Resolve the request fields
     *
     * Ensures that no fields can be injected
     * This function removes the fields that the user is not authorized to see
     *
     * @return \App\Innoclapps\Actions\ActionFields
     */
    public function resolveFields() : ActionFields
    {
        return new ActionFields($this->action()->resolveFields($this)->mapWithKeys(function ($field) {
            return $field->storageAttributes($this, $field->requestAttribute());
        })->all());
    }

    /**
     * Validate the given fields.
     *
     * @return void
     */
    public function validateFields()
    {
        $this->validate($this->action()->resolveFields($this)
            ->mapWithKeys(fn ($field) => $field->getRules())->all());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'array',
        ];
    }

    /**
     * Get the possible actions for the request.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function availableActions()
    {
        // We will get the actual global actions that are defined for the resource
        // as well any actions defined in the resource table classes, as if we don't
        // check the defined actions in table a 404 error will be thrown as the action won't exists
        // in the resource e.q. possible usage, custom actions defined in resource table class or
        // trashed resource table restore and delete actions
        return $this->resource()->resolveActions($this)->merge(
            $this->resourceTable()->resolveActions($this)->all()
        );
    }

    /**
     * Get the resource table class
     *
     * @return \App\Innoclapps\Table\Table
     */
    protected function resourceTable() : Table
    {
        return $this->boolean('trashed') ?
            $this->resource()->resolveTrashedTable(app(ResourceRequest::class)) :
            $this->resource()->resolveTable(app(ResourceRequest::class));
    }

    /**
     * Determine if the specified action exists at all.
     *
     * @return boolean
     */
    protected function actionExists() : bool
    {
        $definedActionsFromTable = $this->resourceTable()->actions($this);

        if (! is_array($definedActionsFromTable)) {
            $definedActionsFromTable = $definedActionsFromTable->all();
        }

        return collect($this->resource()->actions($this))->merge($definedActionsFromTable)
            ->contains(fn ($action) => $action->uriKey() == $this->route('action'));
    }
}
