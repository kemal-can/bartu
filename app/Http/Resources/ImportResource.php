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

class ImportResource extends JsonResource
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
            'file_name'     => $this->file_name,
            'mappings'      => $this->data['mappings'],
            'resource_name' => $this->resource_name,
            'status'        => $this->status,
            'imported'      => $this->imported,
            'skipped'       => $this->skipped,
            'duplicates'    => $this->duplicates,
            'fields'        => $this->fields(),
            'user_id'       => $this->user_id,
            'user'          => new UserResource($this->whenLoaded('user')),
            'completed_at'  => $this->completed_at,
        ], $request);
    }
}
