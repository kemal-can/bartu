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

namespace App\Innoclapps\Facades;

use App\Innoclapps\Fields\Manager;
use Illuminate\Support\Facades\Facade;

class Fields extends Facade
{
    /**
     * The create view name
     */
    const CREATE_VIEW = 'create';

    /**
     * The update view name
     */
    const UPDATE_VIEW = 'update';

    /**
     * The detail view name
     */
    const DETAIL_VIEW = 'detail';

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
