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

namespace App\Resources\Product;

use App\Innoclapps\Table\Table;
use App\Resources\Actions\RestoreAction;
use App\Resources\Actions\ForceDeleteAction;

class ProductTable extends Table
{
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
        $this->orderBy('is_active', 'desc')->orderBy('name', 'asc');
    }
}
