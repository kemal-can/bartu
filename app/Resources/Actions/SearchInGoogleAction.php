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

namespace App\Resources\Actions;

use App\Innoclapps\Actions\Action;
use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;

class SearchInGoogleAction extends Action
{
    /**
     * Indicates that this action is without confirmation dialog
     *
     * @var boolean
     */
    public bool $withoutConfirmation = true;

    /**
     * Indicates that the action will be hidden on the index view
     *
     * @var boolean
     */
    public bool $hideOnIndex = true;

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
        return static::openInNewTab('https://www.google.com/search?q=' . urlencode($models->first()->display_name));
    }

    /**
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illumindate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        return $request->user()->can('view', $model);
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return __('actions.search_in_google');
    }
}
