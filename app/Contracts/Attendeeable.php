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

interface Attendeeable
{
    /**
     * Get the person email address
     *
     * @return string|null
     */
    public function getGuestEmail() : ?string;

    /**
     * Get the person displayable name
     *
     * @return string
     */
    public function getGuestDisplayName() : string;

    /**
     * Get the notification that should be sent to the person when is added as guest
     *
     * @return \Illuminate\Mail\Mailable|\Illuminate\Notifications\Notification
     */
    public function getAttendeeNotificationClass();

    /**
     * Indicates whether the attending notification should be send to the guest
     *
     * @param \App\Contracts\Attendeeable $model
     *
     * @return boolean
     */
    public function shouldSendAttendingNotification(Attendeeable $model) : bool;
}
