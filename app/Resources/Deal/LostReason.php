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

namespace App\Resources\Deal;

use Illuminate\Http\Request;
use App\Innoclapps\Fields\Textarea;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\LostReasonResource;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\LostReasonRepository;

class LostReason extends Resource implements Resourceful
{
    /**
     * The column the records should be default ordered by when retrieving
     *
     * @var string
     */
    public static string $orderBy = 'name';

    /**
     * Get the underlying resource repository
     *
     * @return \App\Innoclapps\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(LostReasonRepository::class);
    }

    /**
     * Set the available resource fields
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request) : array
    {
        return [
            Textarea::make('name', __('deal.lost_reasons.name'))->rules('required', 'string', 'max:191')->unique(static::model()),
        ];
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string
      */
    public function jsonResource() : string
    {
        return LostReasonResource::class;
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel() : string
    {
        return __('deal.lost_reasons.lost_reason');
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label() : string
    {
        return __('deal.lost_reasons.lost_reasons');
    }
}
