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

class ActivityResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource into an array.
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'is_reminded'  => $this->isReminded,
            'is_completed' => $this->isCompleted,
            'completed_at' => $this->completed_at,
            'is_due'       => $this->is_due,
            'created_by'   => $this->created_by,
            'creator'      => new UserResource($this->whenLoaded('creator')),
            $this->mergeWhen(! $request->isZapier(), [
               'comments'       => CommentResource::collection($this->whenLoaded('comments')),
               'comments_count' => (int) $this->comments_count ?: 0,
               'media'          => MediaResource::collection($this->whenLoaded('media')),
            ]),
        ], $request);
    }
}
