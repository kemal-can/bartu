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

namespace App\Innoclapps\Macros\Arr;

class ToObject
{
    public function __invoke($array)
    {
        if (! is_array($array) && ! is_object($array)) {
            return new \stdClass();
        }

        return json_decode(json_encode((object) $array));
    }
}
