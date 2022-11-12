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

use JsonSerializable;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Contracts\Support\Arrayable;
use App\Innoclapps\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
    /**
     * Top level resource actions
     *
     * @var null|\Illuminate\Support\Collection|array
     */
    protected $actions;

    /**
     * Provide common data for the resource
     *
     * @param array $data
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    protected function withCommonData($data, $request)
    {
        $data = parent::withCommonData($data, $request);

        if ($resource = Innoclapps::resourceByModel($this->resource)) {
            $this->mergeFieldsFromResource($resource, $data, $request);
        }

        if (! $request->isZapier()) {
            $data[] = $this->mergeWhen(! is_null($this->actions), [
                'actions' => $this->actions,
            ]);
        }

        return $data;
    }

    /**
     * Merge the request fields in the resource
     *
     * @param \App\Innoclapps\Resource\Resource $resource
     * @param array $data
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     */
    protected function mergeFieldsFromResource($resource, &$data, $request)
    {
        $fields = $resource->getFieldsForJsonResource($request, $this->resource, $this->userCanViewCurrentResource());

        foreach ($fields as $field) {
            $data[] = $this->merge($field->resolveForJsonResource($this->resource));
        }
    }

    /**
     * Check whether the current user can see the current resource
     *
     * @return boolean
     */
    protected function userCanViewCurrentResource()
    {
        // The wasRecentlyCreated check is performed for new main resource
        // e.q. in the front end the main resource is still shown after is created
        // to the user even if don't have permissions to view
        // e.q. regular user created a company and assigned to another user
        // after the company is assigned to another user, the creator won't
        // be able to see the company directly after creation
        // Because the front end still shows the fully create company after creation
        // In this case, to prevent showing 403 immediately we show the user the entity but after he navigated from
        // the entity view or tried to update, it will show the 403 error
        if ($this->wasRecentlyCreated) {
            return true;
        }

        return Auth::user()->can('view', $this->resource);
    }

    /**
     * Set top level resource actions (only for single models)
     *
     * @param \Illuminate\Support\Collection|array $actions
     *
     * @return static
     */
    public function withActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Resolve the resource to an array.
     *
     * @param \Illuminate\Http\Request|null $request
     * @return array
     */
    public function resolve($request = null)
    {
        $data = $this->toArray(
            $request = $request ?: app(ResourceRequest::class)
        );

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return $this->filter((array) $data);
    }

    /**
     * Transform the resource into an HTTP response.
     *
     * @param \Illuminate\Http\Request|null $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($request = null)
    {
        return $this->toResponse(
            $request ?: app(ResourceRequest::class)
        );
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->resolve(app(ResourceRequest::class));
    }
}
