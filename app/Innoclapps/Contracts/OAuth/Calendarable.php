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

namespace App\Innoclapps\Contracts\OAuth;

interface Calendarable
{
    /**
     * Get the OAuth account calendars
     *
     * @return \App\Innoclapps\Contracts\Calendar\Calendar[]
     */
    public function getCalendars();
}
