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

namespace App\Innoclapps\Contracts\Calendar;

interface Calendar
{
    /**
     * Get the calendar ID
     *
     * @return int|string
     */
    public function getId() : int|string;

    /**
     * Get the calendar title
     *
     * @return string
     */
    public function getTitle() : string;
}
