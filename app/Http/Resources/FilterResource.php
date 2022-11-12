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

class FilterResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'name'              => $this->name,
            'identifier'        => $this->identifier,
            'rules'             => $this->rules,
            'user_id'           => $this->user_id,
            'is_shared'         => $this->is_shared,
            'is_system_default' => $this->is_system_default,
            'is_readonly'       => $this->is_readonly,
            'defaults'          => $this->defaults->map(function ($default) {
                return [
                    'user_id' => $default->user_id,
                    'view'    => $default->view,
                ];
            })->values(),
        ], $request);
    }
}
