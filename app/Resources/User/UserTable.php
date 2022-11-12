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

use App\Innoclapps\Table\ID;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Table\Column;
use App\Innoclapps\Table\BooleanColumn;
use App\Innoclapps\Table\DateTimeColumn;

class UserTable extends Table
{
    /**
     * Indicates whether the user can customize columns orders and visibility
     *
     * @var boolean
     */
    public bool $customizeable = true;

    /**
     * Provides table available default columns
     *
     * @return array
     */
    public function columns() : array
    {
        return [
            ID::make(__('app.id')),

            Column::make('name', __('user.name'))->minWidth('200px'),

            Column::make('email', __('user.email'))
                ->queryWhenHidden(),

            Column::make('timezone', __('app.timezone'))->hidden(),

            BooleanColumn::make('super_admin', __('user.super_admin')),

            BooleanColumn::make('access_api', __('api.access'))->hidden(),

            DateTimeColumn::make('created_at', __('app.created_at'))->hidden(),

            DateTimeColumn::make('updated_at', __('app.updated_at'))->hidden(),
        ];
    }

    /**
     * Additional fields to be selected with the query
     *
     * @return array
     */
    public function addSelect() : array
    {
        return ['avatar', 'super_admin'];
    }

    /**
     * Set appends
     *
     * @return array
     */
    protected function appends() : array
    {
        return ['avatar_url'];
    }

    /**
     * Boot table
     *
     * @return null
     */
    public function boot() : void
    {
        $this->orderBy('name', 'asc');
    }
}
