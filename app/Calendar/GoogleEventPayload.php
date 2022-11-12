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

class GoogleEventPayload implements Arrayable
{
    /**
     * Initialize new GoogleEventPayload class
     *
     * @param \App\Models\Activity $activity
     */
    public function __construct(protected Activity $activity)
    {
    }

    /**
     * Get the event summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->activity->title;
    }

    /**
     * Get the event reminder minutes before start
     *
     * @return int|null
     */
    public function getReminders()
    {
        $minutes = $this->activity->reminder_minutes_before;

        if (! $minutes) {
            // makes sure no reminders are configured for the event
            return ['useDefault' => false];
        }

        return [
            'useDefault' => false,
            'overrides'  => [
                ['method' => 'email', 'minutes' => $minutes],
                ['method' => 'popup', 'minutes' => $minutes],
            ],
        ];
    }

    /**
     * Get the event start date
     *
     * @return array
     */
    public function getStartDate()
    {
        $startDate = Carbon::parse($this->activity->full_due_date);
        $allDay    = $this->activity->isAllDay();

        $dateKey = $allDay ? 'date' : 'dateTime';

        return [
            $dateKey   => $allDay ? $startDate->format('Y-m-d') : $startDate->format('Y-m-d\TH:i:s'),
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

        $allDay = $this->activity->isAllDay();

        if (! $this->activity->end_time && ! $allDay) {
            $startDate = Carbon::parse($this->activity->full_due_date);
            $endDate->seconds($startDate->second);
            $endDate->minutes($startDate->minute);
            $endDate->hours($startDate->hour);
        }

        $dateKey = $allDay ? 'date' : 'dateTime';

        return [
            $dateKey   => $allDay ? $endDate->format('Y-m-d') : $endDate->format('Y-m-d\TH:i:s'),
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
                'email'       => $guest->guestable->getGuestEmail(),
                'displayName' => $guest->guestable->getGuestDisplayName(),
            ];
        })->all();
    }

    /**
     * Get the event description
     *
     * @return array
     */
    public function getDescription()
    {
        return $this->activity->description ?: '';
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'summary'     => $this->getSummary(),
            'description' => $this->getDescription(),
            'start'       => $this->getStartDate(),
            'end'         => $this->getEndDate(),
            'attendees'   => $this->getAttendees(),
            'reminders'   => $this->getReminders(),
        ];
    }
}
