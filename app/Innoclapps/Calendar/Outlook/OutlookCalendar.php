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

namespace App\Innoclapps\Calendar\Outlook;

use App\Innoclapps\Facades\Microsoft as Api;
use App\Innoclapps\OAuth\AccessTokenProvider;
use App\Innoclapps\Contracts\OAuth\Calendarable;
use Microsoft\Graph\Model\Calendar as CalendarModel;
use App\Innoclapps\OAuth\Exceptions\ConnectionErrorException;

class OutlookCalendar implements Calendarable
{
    /**
     * Initialize new OutlookCalendar instance.
     *
     * @param \App\Innoclapps\OAuth\AccessTokenProvider $token
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Api::connectUsing($token);
    }

    /**
     * Get the available calendars
     *
     * @return \App\Innoclapps\Contracts\Calendar\Calendar[]
     */
    public function getCalendars()
    {
        $iterator = Api::createCollectionGetRequest('/me/calendars')->setReturnType(CalendarModel::class);

        return collect($this->iterateRequest($iterator))
            ->map(fn ($calendar) => new Calendar($calendar))
            ->values()
            ->all();
    }

    /**
     * Itereate the request pages and get all the data
     *
     * @param \Iterator $iterator
     *
     * @return array
     */
    protected function iterateRequest($iterator)
    {
        try {
            return Api::iterateCollectionRequest($iterator);
        } catch (IdentityProviderException $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
