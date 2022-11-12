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

class PredefinedMailTemplateResource extends JsonResource
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
            'name'      => $this->name,
            'subject'   => $this->subject,
            'body'      => $this->body,
            'is_shared' => $this->is_shared,
            'user_id'   => $this->user_id,
            'user'      => new UserResource($this->whenLoaded('user')),
        ], $request);
    }
}
