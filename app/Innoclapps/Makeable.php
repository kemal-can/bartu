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

namespace App\Innoclapps;

trait Makeable
{
    /**
     * Create new instance
     *
     * @param array $params
     *
     * @return static
     */
    public static function make(...$params) : static
    {
        return new static(...$params);
    }
}
