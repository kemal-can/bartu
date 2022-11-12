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

namespace App\Innoclapps\Updater\Exceptions;

use Exception;

class PurchaseKeyUsedException extends UpdaterException
{
    /**
     * Initialize new PurchaseKeyUsedException instance
     *
     * @param string $message
     * @param integer $code
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct('Purchase key already used to download the files for the requested release.', 409, $previous);
    }
}
