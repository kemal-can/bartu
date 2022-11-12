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

class NoteResource extends JsonResource
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
            'body'      => clean($this->body),
            'user_id'   => $this->user_id,
            'user'      => new UserResource($this->whenLoaded('user')),
            'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            'contacts'  => ContactResource::collection($this->whenLoaded('contacts')),
            'deals'     => DealResource::collection($this->whenLoaded('deals')),
            $this->mergeWhen(! $request->isZapier(), [
               'comments'       => CommentResource::collection($this->whenLoaded('comments')),
               'comments_count' => (int) $this->comments_count ?: 0,
            ]),
        ], $request);
    }
}
