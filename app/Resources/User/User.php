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

namespace App\Resources\User;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Rules\UniqueResourceRule;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Contracts\Repositories\UserRepository;
use App\Innoclapps\Rules\ValidTimezoneCheckRule;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Resources\Resourceful;

class User extends Resource implements Resourceful, Tableable
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
        return resolve(UserRepository::class);
    }

    /**
    * Provide the resource table class
    *
    * @param \App\Innoclapps\Repository\BaseRepository $repository
    * @param \Illuminate\Http\Request $request
    *
    * @return \App\Innoclapps\Table\Table
    */
    public function table($repository, Request $request) : Table
    {
        return new UserTable($repository, $request);
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return UserResource::class;
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
            'name'     => ['required', 'string', 'max:191'],
            'password' => [
                $request->route('resourceId') ? 'nullable' : 'required', 'confirmed', 'min:6',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                UniqueResourceRule::make(static::model()),
            ],
            'locale'            => ['nullable', Rule::in(Translation::availableLocales())],
            'timezone'          => ['required', 'string', new ValidTimezoneCheckRule],
            'time_format'       => ['required', 'string', Rule::in(config('app.time_formats'))],
            'date_format'       => ['required', 'string', Rule::in(config('app.date_formats'))],
            'first_day_of_week' => ['required', 'in:1,6,0', 'numeric'],
        ];
    }

    /**
     * Provides the resource available actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function actions(ResourceRequest $request) : array
    {
        return [
            (new Actions\UserDelete)->canSeeWhen('is-super-admin'),
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
            SettingsMenuItem::make(__('user.users'), '/settings/users', 'Users')->order(41),
        ];
    }
}
