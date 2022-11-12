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

class Html2Text
{
    /**
     * Convert HTML to Text
     *
     * @param string $html
     *
     * @return string
     */
    public static function convert($html)
    {
        return \Soundasleep\Html2Text::convert($html, ['ignore_errors' => true]);
    }
}
