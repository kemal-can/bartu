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

class CastValuesAsString
{
    /**
     * Cast the provided array values as string
     *
     * @param array $array
     *
     * @return array
     */
    public function __invoke($array)
    {
        return array_map(fn ($value) => (string) $value, $array);
    }
}
