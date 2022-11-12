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

use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
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
            'id'                 => $this->guestable->getKey(),
            'guest_email'        => $this->guestable->getGuestEmail(),
            'guest_display_name' => $this->guestable->getGuestDisplayName(),
            'resource_name'      => Innoclapps::resourceByModel($this->guestable)->name(),
        ];
    }
}
