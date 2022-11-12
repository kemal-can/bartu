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

namespace App\Innoclapps\Import\Exceptions;

use Exception;

class ImportException extends Exception
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 500;

    /**
     * The validation errors
     *
     * @var array
     */
    public $errors = [];

    /**
     * Init the exception
     */
    public function __construct($message, $errors = [])
    {
        $this->errors = $errors;

        parent::__construct($message, $this->status);
    }

    /**
     * Get the import errors/validation errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}
