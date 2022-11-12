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

use Illuminate\Http\Resources\Json\JsonResource;
use App\Contracts\Repositories\CalendarRepository;

class CalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                           => $this->id,
            'email'                        => $this->email,
            'calendar_id'                  => $this->calendar_id,
            'activity_type_id'             => $this->activity_type_id,
            'activity_types'               => $this->activity_types,
            'start_sync_from'              => $this->startSyncFrom(),
            'user_id'                      => $this->user_id,
            'user'                         => new UserResource($this->whenLoaded('user')),
            'account'                      => new OAuthAccountResource($this->whenLoaded('oAuthAccount')),
            'last_sync_at'                 => $this->synchronization->last_synchronized_at,
            'is_sync_disabled'             => $this->synchronization->isSyncDisabled(),
            'is_sync_stopped'              => $this->synchronization->isSyncStoppedBySystem(),
            'is_synchronizing_via_webhook' => $this->synchronization->isSynchronizingViaWebhook(),
            'sync_state_comment'           => $this->synchronization->sync_state_comment,
            'previously_used'              => resolve(CalendarRepository::class)
                ->with('synchronization')
                ->findWhere(['user_id' => $this->user_id, ['id', '!=', $this->id]])->map(function ($calendar) {
                    return [
                        'id'              => $calendar->id,
                        'calendar_id'     => $calendar->calendar_id,
                        'start_sync_from' => $calendar->synchronization->start_sync_from,
                        'created_at'      => $calendar->synchronization->created_at,
                    ];
                })->sortBy('created_at'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
