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

namespace App\Contracts;

use Illuminate\Database\Query\Expression;

interface Calendarable
{
    /**
     * Get the start date
     *
     * @return string
     */
    public function getCalendarStartDate() : string;

    /**
     * Get the end date
     *
     * @return string
     */
    public function getCalendarEndDate() : string;

    /**
     * Indicates whether the event is all day
     *
     * @return boolean
     */
    public function isAllDay() : bool;

    /**
     * Get the displayable title for the calendar
     *
     * @return string
     */
    public function getCalendarTitle() : string;

    /**
     * Get the calendar start date column name for query
     *
     * @return string
     */
    public static function getCalendarStartColumnName() : string|Expression;

    /**
     * Get the calendar end date column name for query
     *
     * @return string
     */
    public static function getCalendarEndColumnName() : string|Expression;
}
