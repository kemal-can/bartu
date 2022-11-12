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

namespace App\Resources\Company;

use App\Innoclapps\Table\Table;
use App\Innoclapps\Table\BelongsToColumn;
use App\Resources\Actions\RestoreAction;
use App\Resources\Actions\ForceDeleteAction;

class CompanyTable extends Table
{
    /**
     * Indicates whether the user can customize columns orders and visibility
     *
     * @var boolean
     */
    public bool $customizeable = true;

    /**
     * Additional fields to be selected with the query
     *
     * @return array
     */
    protected function addSelect() : array
    {
        return [
             // The user_id must remains even if the BelongsToColumn::make('owner') is removed
            'user_id', // is for the policy checks,
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
