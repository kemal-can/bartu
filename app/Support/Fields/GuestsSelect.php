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

namespace App\Support\Fields;

use App\Innoclapps\Fields\Field;
use App\Http\Resources\GuestResource;

class GuestsSelect extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'guests-select-field';

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => []);
    }

    /**
     * Resolve the displayable field value (for mail placeholders)
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        if ($value->isNotEmpty()) {
            $value->loadMissing('guestable');

            return $value->map(fn ($guest) => $guest->guestable)->map->getGuestDisplayName()->implode(', ');
        }

        return null;
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if ($model->relationLoaded('guests')) {
            return ['guests' => GuestResource::collection($model->guests)];
        }

        return null;
    }
}
