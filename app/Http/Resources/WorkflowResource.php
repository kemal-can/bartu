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

class WorkflowResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'title'            => $this->title,
            'description'      => $this->description,
            'is_active'        => $this->is_active,
            'total_executions' => $this->total_executions,
            'trigger_type'     => $this->trigger_type,
            'action_type'      => $this->action_type,
            'data'             => $this->data,
        ], $request);
    }
}
