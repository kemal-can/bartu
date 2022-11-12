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

namespace App\Innoclapps\Zapier;

use Exception;

class ActionNotSupportedException extends Exception
{
    /**
     * Initialize ActionNotSupportedException
     */
    public function __construct($action, $code = 0, Exception $previous = null)
    {
        parent::__construct("$action is not supported.", $code, $previous);
    }
}
