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

use App\Innoclapps\ProvidesModelAuthorizations;
use Illuminate\Http\Resources\Json\JsonResource;

class DealBoardResource extends JsonResource
{
    use ProvidesModelAuthorizations;

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
            'id'              => $this->id,
            'name'            => $this->name,
            'win_probability' => $this->win_probability,
            'display_order'   => $this->display_order,
            'summary'         => $this->summary,
            'cards'           => $this->deals->map(function ($deal) {
                return with([
                    'id'                                   => $deal->id,
                    'name'                                 => $deal->name, // for activity create modal
                    'amount'                               => $deal->amount,
                    'display_name'                         => $deal->display_name,
                    'path'                                 => $deal->path,
                    'authorizations'                       => $this->getAuthorizations($deal),
                    'expected_close_date'                  => $deal->expected_close_date,
                    'incomplete_activities_for_user_count' => (int) $deal->incomplete_activities_for_user_count,
                    'user_id'                              => $deal->user_id,
                    'swatch_color'                         => $deal->swatch_color,
                ], function ($attributes) use ($deal) {
                    if (! is_null($deal->expected_close_date)) {
                        $attributes['falls_behind_expected_close_date'] = $deal->fallsBehindExpectedCloseDate;
                    }

                    return $attributes;
                });
            })->values(),
        ];
    }
}
