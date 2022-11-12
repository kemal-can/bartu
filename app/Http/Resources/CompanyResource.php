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

use App\Innoclapps\Resources\Http\JsonResource;

class CompanyResource extends JsonResource
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
        ChangelogResource::topLevelResource($this->resource);

        return $this->withCommonData([

            'notes_count' => $this->whenCounted('notes', fn () => (int) $this->notes_count),
            'calls_count' => $this->whenCounted('calls', fn () => (int) $this->calls_count),

            $this->mergeWhen(! $request->isZapier() && $this->userCanViewCurrentResource(), [
                'parents'    => CompanyResource::collection($this->whenLoaded('parents')),
                'changelog'  => ChangelogResource::collection($this->whenLoaded('changelog')),
                'notes'      => NoteResource::collection($this->whenLoaded('notes')),
                'calls'      => CallResource::collection($this->whenLoaded('calls')),
                'activities' => ActivityResource::collection($this->whenLoaded('activities')),
                'media'      => MediaResource::collection($this->whenLoaded('media')),
                'emails'     => EmailAccountMessageResource::collection($this->whenLoaded('emails')),
            ]),
        ], $request);
    }
}
