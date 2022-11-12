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
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Innoclapps\Resources\Http\ResourceRequest;

class ResourceTableRequest extends ResourceRequest
{
    /**
     * Get the class of the resource being requested.
     *
     * @return \App\Innoclapps\Resources\Resource
     */
    public function resource()
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Tableable, 404);
        });
    }

    /**
     * Resolve the resource table for the current request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function resolveTable() : Table
    {
        return $this->resource()->resolveTable($this);
    }

    /**
     * Resolve the resource trashed table for the current request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function resolveTrashedTable() : Table
    {
        return $this->resource()->resolveTrashedTable($this);
    }
}
