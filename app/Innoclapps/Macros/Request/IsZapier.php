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

class IsZapier
{
    /**
     * Determine whether current request is from Zapier
     *
     * @return boolean
     */
    public function __invoke()
    {
        return request()->header('user-agent') === 'Zapier';
    }
}
