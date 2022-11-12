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

namespace App\Innoclapps\Resources\Http;

use App\Innoclapps\Facades\Innoclapps;

trait InteractsWithResources
{
    /**
     * Custom resource id for the request
     *
     * @var int
     */
    protected $customResourceId;

    /**
     * Custom resource for the request
     *
     * @var string
     */
    protected $customResource;

    /**
     * Resource for the request
     *
     * @var \App\Innoclapps\Resources\Resource
     */
    protected $resource;

    /**
     * The request resource record
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $record;

    /**
     * Get the resource name for the current request
     *
     * @return string
     */
    public function resourceName()
    {
        if ($this->customResource) {
            return $this->customResource;
        }

        return $this->route('resource');
    }

    /**
     * Set custom resource for the request
     *
     * @param string $name
     *
     * @return static
     */
    public function setResource($name)
    {
        $this->customResource = $name;

        $this->resource = null;
        $this->record   = null;

        return $this;
    }

    /**
     * Get the request resource id
     *
     * @return int
     */
    public function resourceId()
    {
        if ($this->customResourceId) {
            return $this->customResourceId;
        }

        return $this->route('resourceId');
    }

    /**
     * Set custom resource id for the request
     *
     * @param int $id
     *
     * @return static
     */
    public function setResourceId($id)
    {
        $this->customResourceId = $id;

        $this->record = null;

        return $this;
    }

    /**
     * Get the class of the resource being requested.
     *
     * @return \App\Innoclapps\Resources\Resource
     */
    public function resource()
    {
        if ($this->resource) {
            return $this->resource;
        }

        return $this->resource = tap(
            $this->findResource($this->resourceName()),
            function ($resource) {
                abort_if(is_null($resource), 404);
            }
        );
    }

    /**
     * Get the resource record for the current request
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function record()
    {
        if (! $this->record) {
            $this->record = $this->resource()->repository()->find($this->resourceId());
        }

        return $this->record;
    }

    /**
     * Get resource by a given name
     *
     * @param string $name
     *
     * @return \App\Innoclapps\Resources\Resource|null
     */
    public function findResource($name)
    {
        if (! $name) {
            return null;
        }

        return Innoclapps::resourceByName($name);
    }
}
