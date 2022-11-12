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

use App\Enums\DealStatus;
use App\Innoclapps\Resources\Http\JsonResource;

class DealResource extends JsonResource
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

            $this->mergeWhen($this->status === DealStatus::lost, [
                'lost_reason' => $this->lost_reason,
                'lost_date'   => $this->lost_date,
            ]),

            $this->mergeWhen($this->status === DealStatus::won, [
                'won_date' => $this->won_date,
            ]),

            'stage_changed_date' => $this->stage_changed_date,

            $this->mergeWhen(! is_null($this->expected_close_date) && ! $request->isZapier(), [
                'falls_behind_expected_close_date' => $this->fallsBehindExpectedCloseDate,
            ]),

            $this->mergeWhen(! $request->isZapier() && $this->userCanViewCurrentResource(), [
                'board_order'    => $this->board_order,
                'time_in_stages' => $this->whenLoaded('stagesHistory', function () {
                    return $this->timeInStages();
                }),
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
