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

namespace App\Installer;

use Exception;

class PrivilegeNotGrantedException extends Exception
{
    /**
     * Get the privilege name that was denied
     *
     * @return string
     */
    public function getPriviligeName()
    {
        $re = '/[0-9]+\s([A-Z]+)+\scommand denied/m';

        preg_match($re, $this->getMessage(), $matches);

        return $matches[1] ?? '';
    }
}
