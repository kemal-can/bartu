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

trait ResolvesFilters
{
    /**
     *  Get the available filters for the user
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveFilters(ResourceRequest $request) : Collection
    {
        $filters = $this->filters($request);

        $collection = is_array($filters) ? new Collection($filters) : $filters;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined filters
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array|Illuminate\Support\Collection
     */
    public function filters(ResourceRequest $request) : array|Collection
    {
        return [];
    }
}
