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

namespace App\Resources;

use Illuminate\Http\Request;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\IndustryResource;
use App\Contracts\Repositories\IndustryRepository;
use App\Innoclapps\Contracts\Resources\Resourceful;

class Industry extends Resource implements Resourceful
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
        return resolve(IndustryRepository::class);
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
            Text::make('name', __('company.industry.industry'))->rules('required', 'string', 'max:191')->unique(static::model()),
        ];
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel() : string
    {
        return __('company.industry.industry');
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label() : string
    {
        return __('company.industry.industries');
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string
      */
    public function jsonResource() : string
    {
        return IndustryResource::class;
    }
}
