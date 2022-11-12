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

use App\Innoclapps\JsonResource;

class MediaResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'file_name'    => $this->basename,
            'extension'    => $this->extension,
            'size'         => $this->size,
            'disk_path'    => $this->getDiskPath(),
            'mime_type'    => $this->mime_type,
            'view_url'     => $this->getViewUrl(),
            'preview_url'  => $this->getPreviewUrl(),
            'download_url' => $this->getDownloadUrl(),
            'pending_data' => $this->whenLoaded('pendingData'),
            'tag'          => $this->pivot->tag ?? null,
        ], $request);
    }
}
