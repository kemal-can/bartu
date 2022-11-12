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

namespace App\Innoclapps\Macros\Request;

class IsSearching
{
    /**
     * Determine whether user is performing search via the repository RequestCriteria
     *
     * @return boolean
     */
    public function __invoke()
    {
        return ! is_null(request()->get('q', null));
    }
}
