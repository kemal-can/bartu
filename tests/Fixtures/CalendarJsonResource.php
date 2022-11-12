<?php

namespace Tests\Fixtures;

use App\Innoclapps\Resources\Http\JsonResource;

class CalendarJsonResource extends JsonResource
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
            'title' => $this->title,
        ];
    }
}
