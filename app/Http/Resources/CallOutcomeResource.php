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

class CallOutcomeResource extends JsonResource
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
            'name' => $this->name,
            $this->mergeWhen(! $request->isZapier(), [
                'swatch_color' => $this->swatch_color,
            ]),
        ], $request);
    }
}
