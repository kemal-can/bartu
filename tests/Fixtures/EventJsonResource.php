<?php

namespace Tests\Fixtures;

use App\Http\Resources\ProvidesCommonData;
use App\Innoclapps\Resources\Http\JsonResource;

class EventJsonResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
        ], $request);
    }
}
