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

namespace App\Innoclapps;

use Illuminate\Support\Collection;
use App\Innoclapps\Resources\Http\ResourceRequest;

trait ResolvesActions
{
    /**
     * Get the available actions for the resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveActions(ResourceRequest $request) : Collection
    {
        $actions = $this->actions($request);

        $collection = is_array($actions) ? new Collection($actions) : $actions;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined resource actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array|Illuminate\Support\Collection
     */
    public function actions(ResourceRequest $request) : array|Collection
    {
        return [];
    }
}
