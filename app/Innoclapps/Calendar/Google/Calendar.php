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

use App\Innoclapps\Calendar\AbstractCalendar;
use App\Innoclapps\Contracts\Calendar\Calendar as CalendarInterface;

class Calendar extends AbstractCalendar implements CalendarInterface
{
    /**
     * Get the calendar ID
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get the calendar title
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->getEntity()->getSummary();
    }
}
