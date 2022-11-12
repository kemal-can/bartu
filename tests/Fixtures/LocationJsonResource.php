<?php

namespace Tests\Fixtures;

use App\Innoclapps\JsonResource;

class LocationJsonResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'display_name'  => $this->display_name,
            'location_type' => $this->location_type,
        ];
    }
}
