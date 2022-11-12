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

class EmailAccountFolderResource extends JsonResource
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
            'id'               => $this->id,
            'parent_id'        => $this->parent_id,
            'email_account_id' => $this->email_account_id,
            'remote_id'        => $this->remote_id,
            'name'             => $this->name,
            'display_name'     => $this->display_name,
            'syncable'         => $this->syncable,
            'selectable'       => $this->selectable,
            'unread_count'     => (int) $this->unread_count ?: 0,
            'type'             => $this->type,
        ];
    }
}
