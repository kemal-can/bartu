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

class CannotOpenZipArchiveException extends UpdaterException
{
    /**
     * Initialize new CannotOpenZipArchiveException instance
     *
     * @param string $filePath
     * @param integer $code
     * @param \Exception|null $previous
     */
    public function __construct($filePath = '', $code = 0, Exception $previous = null)
    {
        parent::__construct('Cannot open zip archive. [' . $filePath . ']', 500, $previous);
    }
}
