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

namespace App\Innoclapps\Calendar\Google;

use App\Innoclapps\Facades\Google as Client;
use App\Innoclapps\OAuth\AccessTokenProvider;
use App\Innoclapps\Contracts\OAuth\Calendarable;

class GoogleCalendar implements Calendarable
{
    /**
     * Initialize new GoogleCalendar instance.
     *
     * @param \App\Innoclapps\OAuth\AccessTokenProvider $token
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Client::connectUsing($token->getEmail());
    }

    /**
     * Get the available calendars
     *
     * @return \App\Innoclapps\Contracts\Calendar\Calendar[]
     */
    public function getCalendars()
    {
        return collect(Client::calendar()->list())
            ->map(fn ($calendar) => new Calendar($calendar))
            ->values()
            ->all();
    }
}
