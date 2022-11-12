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
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Fields\Editor;
use App\Innoclapps\Fields\DateTime;
use App\Http\Resources\CallResource;
use App\Innoclapps\Fields\BelongsTo;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Criteria\RelatedCriteria;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Contracts\Repositories\CallRepository;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\CallOutcomeRepository;

class Call extends Resource implements Resourceful
{
    /**
    * Get the underlying resource repository
    *
    * @return \App\Innoclapps\Repository\AppRepository
    */
    public static function repository()
    {
        return resolve(CallRepository::class);
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return CallResource::class;
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string|null
    */
    public function ownCriteria() : ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return RelatedCriteria::class;
        }

        return null;
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
            BelongsTo::make('outcome', resolve(CallOutcomeRepository::class), __('call.outcome.outcome'))
                ->rules(['required', 'numeric'])
                ->withMeta([
                'attributes' => [
                    'clearable'   => false,
                    'placeholder' => __('call.outcome.select_outcome'),
                ],
                ])
                ->colClass('col-span-12 sm:col-span-6'),

                DateTime::make('date', __('call.date'))->withDefaultValue(Carbon::parse())
                    ->colClass('col-span-12 sm:col-span-6')
                    ->rules('required', 'date'),

                Editor::make('body')->rules('requ   ired', 'string')
                    ->validationMessages(['required' => __('validation.required_without_label')])
                    ->withMeta([
                    'attributes' => [
                        'placeholder'  => __('call.log'),
                        'with-mention' => true,
                    ],
                ]),
            ];
    }

    /**
    * Get the resource available cards
    *
    * @return array
    */
    public function cards() : array
    {
        return [
                (new Cards\LoggedCallsByDay)->withUserSelection()->canSeeWhen('is-super-admin'),
                (new Cards\LoggedCallsBySaleAgent)->canSeeWhen('is-super-admin')->color('success'),
                (new Cards\OverviewByCallOutcome)->color('info')->withUserSelection(function () {
                    return auth()->user()->isSuperAdmin();
                }),
            ];
    }

    /**
    * Get the resource relationship name when it's associated
    *
    * @return string
    */
    public function associateableName() : string
    {
        return 'calls';
    }

    /**
    * Get the relations to eager load when quering associated records
    *
    * @return array
    */
    public function withWhenAssociated() : array
    {
        return ['user', 'outcome'];
    }

    /**
     * Get the countable relations when quering associated records
     *
     * @return array
     */
    public function withCountWhenAssociated() : array
    {
        return ['comments'];
    }

    /**
    * Get the resource rules available for create and update
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function rules(Request $request)
    {
        return [
                'via_resource'    => 'required|string',
                'via_resource_id' => 'required|numeric',
            ];
    }

    /**
     * Register the settings menu items for the resource
     *
     * @return array
     */
    public function settingsMenu() : array
    {
        return [
            SettingsMenuItem::make(__('call.calls'), '/settings/calls', 'DeviceMobile')->order(25),
        ];
    }
}
