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

use App\Models\Activity;
use App\Innoclapps\Date\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class OutlookEventPayload implements Arrayable
{
    /**
     * Initialize new OutlookEventPayload class
     *
     * @param \App\Models\Activity $activity
     */
    public function __construct(protected Activity $activity)
    {
    }

    /**
     * Get the event subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->activity->title;
    }

    /**
     * Get is all day event
     *
     * @return boolean
     */
    public function getIsAllDay()
    {
        return $this->activity->isAllDay();
    }

    /**
     * Get the event reminder minutes before start
     *
     * @return int|null
     */
    public function getReminderMinutesBeforeStart()
    {
        return $this->activity->reminder_minutes_before;
    }

    /**
     * Get the event start date
     *
     * @return array
     */
    public function getStartDate()
    {
        $startDate = Carbon::parse($this->activity->full_due_date);

        if ($this->activity->isAllDay()) {
            $startDate->seconds(0)->minutes(0)->hours(0);
        }

        return [
            'dateTime' => $startDate->format('Y-m-d\TH:i:s'),
            'timeZone' => config('app.timezone'),
        ];
    }

    /**
     * Get the event end date
     *
     * @return array
     */
    public function getEndDate()
    {
        $endDate = Carbon::parse($this->activity->full_end_date);

        if ($this->activity->isAllDay()) {
            $endDate->addDay(1)->seconds(0)->minutes(0)->hours(0);
        } elseif (! $this->activity->end_time) {
            $startDate = Carbon::parse($this->activity->full_due_date);
            $endDate->seconds($startDate->second);
            $endDate->minutes($startDate->minute);
            $endDate->hours($startDate->hour);
        }

        return [
            'dateTime' => $endDate->format('Y-m-d\TH:i:s'),
            'timeZone' => config('app.timezone'),
        ];
    }

    /**
     * Get the event attendees
     *
     * @return array
     */
    public function getAttendees()
    {
        return $this->activity->guests->map(function ($guest) {
            return [
                'type'         => 'optional',
                'emailAddress' => [
                    'address' => $guest->guestable->getGuestEmail(),
                    'name'    => $guest->guestable->getGuestDisplayName(),
                ],
            ];
        })->all();
    }

    /**
     * Get the event body
     *
     * @return array
     */
    public function getBody()
    {
        return [
            'contentType' => 'HTML',
            'content'     => $this->activity->description ?: '',
        ];
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'subject'                    => $this->getSubject(),
            'body'                       => $this->getBody(),
            'start'                      => $this->getStartDate(),
            'end'                        => $this->getEndDate(),
            'attendees'                  => $this->getAttendees(),
            'isAllDay'                   => $this->getIsAllDay(),
            'reminderMinutesBeforeStart' => $this->getReminderMinutesBeforeStart(),
        ];
    }
}
