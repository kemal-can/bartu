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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    use InteractsWithResources;

    /**
     * Resolve the resource json resource and create appropriate response
     *
     * @param mixed $data
     *
     * @return array
     */
    public function toResponse($data)
    {
        if (! $this->resource()->jsonResource()) {
            return $data;
        }

        $jsonResource = $this->resource()->createJsonResource($data);

        if ($data instanceof Model) {
            $jsonResource->withActions(
                $this->resource()->resolveActions($this)
            );
        }

        return $jsonResource->toResponse($this)->getData();
    }

    /**
     * Check whether the current request is for create
     *
     * @return boolean
     */
    public function isCreateRequest()
    {
        return $this->intent == 'create' || $this instanceof CreateResourceRequest;
    }

    /**
     * Check whether the current request is for update
     *
     * @return boolean
     */
    public function isUpdateRequest()
    {
        return $this->intent == 'update' || $this->intent === 'details' || $this instanceof UpdateResourceRequest;
    }

    /**
     * Check whether the current request is via resource
     *
     * @return boolean
     */
    public function viaResource()
    {
        return $this->has('via_resource');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
