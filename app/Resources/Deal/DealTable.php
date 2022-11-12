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

use App\Innoclapps\Table\Table;
use App\Resources\Actions\RestoreAction;
use App\Resources\Actions\ForceDeleteAction;

class DealTable extends Table
{
    /**
     * Indicates whether the user can customize columns orders and visibility
     *
     * @var boolean
     */
    public bool $customizeable = true;

    /**
    * Set appends
    *
    * @return array
    */
    protected function appends() : array
    {
        return [
            'falls_behind_expected_close_date', // row class
        ];
    }

    /**
     * Additional fields to be selected with the query
     *
     * @return array
     */
    public function addSelect() : array
    {
        return [
            'user_id', // user_id is for the policy checks
            'expected_close_date', // falls_behind_expected_close_date check
            'status', // falls_behind_expected_close_date check
        ];
    }

    /**
     * Get the actions intended for the trashed table
     *
     * NOTE: No authorization is perfomrmed on these action, all actions will be visible to the user
     *
     * @return array
     */
    public function actionsForTrashedTable()
    {
        return [new RestoreAction, new ForceDeleteAction];
    }

    /**
     * Boot table
     *
     * @return void
     */
    public function boot() : void
    {
        $this->orderBy('created_at', 'desc');
    }
}
