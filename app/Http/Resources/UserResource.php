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

use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Resources\Http\JsonResource;

class UserResource extends JsonResource
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
            'name'           => $this->name,
            'email'          => $this->email,
            'timezone'       => $this->timezone,
            'mail_signature' => clean($this->mail_signature),
            $this->mergeWhen(! $request->isZapier(), [
                'super_admin'         => $this->super_admin,
                'access_api'          => $this->access_api,
                'locale'              => $this->locale,
                'avatar'              => $this->avatar,
                'uploaded_avatar_url' => $this->uploaded_avatar_url,
                'avatar_url'          => $this->avatar_url,
                'time_format'         => $this->time_format,
                'date_format'         => $this->date_format,
                'first_day_of_week'   => $this->first_day_of_week,
                $this->mergeWhen($this->is(Auth::user()) && $this->relationLoaded('dashboards'), [
                    'dashboards' => DashboardResource::collection($this->whenLoaded('dashboards')),
                ]),
                'notifications' => [
                    $this->mergeWhen($this->is(Auth::user()) &&
                        $this->relationLoaded('latestFifteenNotifications'), function () {
                            return ['latest' => $this->latestFifteenNotifications];
                        }),
                    $this->mergeWhen($this->is(Auth::user()) && ! is_null($this->unread_notifications_count), [
                        'unread_count' => (int) $this->unread_notifications_count,
                    ]),
                    // Admin user edit and profile
                    $this->mergeWhen(Auth::user()->isSuperAdmin() || $this->is(Auth::user()), [
                        'settings' => Innoclapps::notificationsInformation($this->resource),
                    ]),
                ],
                $this->mergeWhen($this->is(Auth::user()), function () {
                    return [
                        'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
                    ];
                }),
                $this->mergeWhen($this->whenLoaded('roles') && Auth::user()->isSuperAdmin(), [
                    'roles' => RoleResource::collection($this->whenLoaded('roles')),
                ]),
                'gate' => [
                    'access-api'     => $this->resource->can('access-api'),
                    'is-super-admin' => $this->resource->isSuperAdmin(),
                ],
            ]),
        ], $request);
    }
}
