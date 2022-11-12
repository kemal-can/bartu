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

namespace App\Http\Resources;

use App\Innoclapps\JsonResource;

class CustomFieldResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'field_type'    => $this->field_type,
            'resource_name' => $this->resource_name,
            'field_id'      => $this->field_id,
            'label'         => $this->label,
            'options'       => $this->when($this->options->isNotEmpty(), $this->options),
        ], $request);
    }
}
