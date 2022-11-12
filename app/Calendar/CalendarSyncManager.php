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

namespace App\Calendar;

use App\Models\Calendar;
use InvalidArgumentException;

class CalendarSyncManager
{
    /**
     * Create calendar synchronizer
     *
     * @param \App\Models\Calendar $calendar
     *
     * @return \App\Calendar\CalendarSynchronization&\App\Contracts\Calendarable\Synchronizable
     */
    public static function createClient(Calendar $calendar)
    {
        $method = 'create' . ucfirst($calendar->connection_type) . 'Driver';

        if (! method_exists(new static, $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve [%s] driver for [%s].',
                $method,
                static::class
            ));
        }

        return self::$method($calendar);
    }

    /**
     * Create the Google calendar sync driver
     *
     * @param \App\Models\Calendar $calendar
     *
     * @return \App\Calendar\GoogleCalendarSync
     */
    public static function createGoogleDriver(Calendar $calendar)
    {
        return new GoogleCalendarSync($calendar);
    }

    /**
     * Create the Outlook calendar sync driver
     *
     * @param \App\Models\Calendar $calendar
     *
     * @return \App\Calendar\OutlookCalendarSync
     */
    public static function createOutlookDriver(Calendar $calendar)
    {
        return new OutlookCalendarSync($calendar);
    }
}
