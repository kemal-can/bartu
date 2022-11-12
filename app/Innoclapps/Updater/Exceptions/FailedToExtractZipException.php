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

class FailedToExtractZipException extends UpdaterException
{
    /**
     * Initialize new FailedToExtractZipException instance
     *
     * @param string $filePath
     * @param integer $code
     * @param \Exception|null $previous
     */
    public function __construct($filePath = '', $code = 0, Exception $previous = null)
    {
        parent::__construct('Failed to extract zip file. [' . $this->filePath . ']', 500, $previous);
    }
}
