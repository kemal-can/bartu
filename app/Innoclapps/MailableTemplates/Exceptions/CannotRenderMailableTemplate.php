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

namespace App\Innoclapps\MailableTemplates\Exceptions;

use Exception;

class CannotRenderMailableTemplate extends Exception
{
    /**
     * Throw exception
     *
     * @throws CannotRenderMailableTemplate
     *
     * @return Exception
     */
    public static function layoutDoesNotContainABodyPlaceHolder()
    {
        return new static('The layout does not contain a `{{{ mailBody }}}` placeholder');
    }
}
