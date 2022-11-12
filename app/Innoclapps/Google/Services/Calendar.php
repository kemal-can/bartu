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

namespace App\Innoclapps\Google\Services;

use Google_Service_Calendar;

class Calendar extends Service
{
    /**
     * Initialize new Service instance
     *
     * @param \Google_Client $client
     */
    public function __construct($client)
    {
        parent::__construct($client, Google_Service_Calendar::class);
    }

    /**
     * List all available user calendars
     *
     * @return \Google_Service_Calendar_CalendarListEntry[]
     */
    public function list()
    {
        $calendars = [];
        $nextPage  = null;

        do {
            $calendarList = $this->service->calendarList->listCalendarList([
                'pageToken' => $nextPage,
            ]);

            foreach ($calendarList->getItems() as $calendar) {
                $calendars[] = $calendar;
            }
        } while (($nextPage = $calendarList->getNextPageToken()));

        return $calendars;
    }

    /**
     * Get calendar by id
     *
     * @param string $id
     *
     * @return \Google_Service_Calendar_CalendarListEntry
     */
    public function get($id)
    {
        return $this->service->calendars->get('me', $id);
    }
}
