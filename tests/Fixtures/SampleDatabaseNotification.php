<?php

namespace Tests\Fixtures;

use App\Innoclapps\Notification;

class SampleDatabaseNotification extends Notification
{
    /**
     * Get the notification available delivery channels
     *
     * @return array
     */
    public static function availableChannels() : array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [

        ];
    }
}
