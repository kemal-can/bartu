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

namespace App\Resources\Call;

use Illuminate\Http\Request;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Fields\ColorSwatches;
use App\Http\Resources\CallOutcomeResource;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\CallOutcomeRepository;

class CallOutcome extends Resource implements Resourceful
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
        return resolve(CallOutcomeRepository::class);
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
            Text::make('name', __('call.outcome.name'))->rules('required', 'string', 'max:191')->unique(static::model()),
            ColorSwatches::make('swatch_color', __('app.colors.color'))->rules('nullable', 'string', 'max:7'),
        ];
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string
      */
    public function jsonResource() : string
    {
        return CallOutcomeResource::class;
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel() : string
    {
        return __('call.outcome.call_outcome');
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label() : string
    {
        return __('call.outcome.outcomes');
    }
}
