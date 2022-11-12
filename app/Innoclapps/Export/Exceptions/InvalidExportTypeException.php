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

namespace App\Innoclapps\Export\Exceptions;

use Exception;

class InvalidExportTypeException extends Exception
{
    /**
     * Create new InvalidExportTypeException instnace.
     *
     * @param string $type
     * @param integer $code
     * @param \Exception|null $previous
     */
    public function __construct($type, $code = 0, Exception $previous = null)
    {
        parent::__construct("The export type \"$type\" is not supported.", $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode() ?: 500);
    }
}
