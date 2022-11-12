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

namespace App\Notifications;

use App\Models\Activity;
use App\Innoclapps\Notification;
use App\Innoclapps\Facades\Format;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\ActivityReminder as ReminderMailable;

class ActivityReminder extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Activity $activity
     *
     * @return void
     */
    public function __construct(protected Activity $activity)
    {
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \App\Innoclapps\MailableTemplates\MailableTemplate
     */
    public function toMail($notifiable)
    {
        return $this->viaMailableTemplate(
            new ReminderMailable($this->activity),
            $notifiable
        );
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
            'path' => $this->activity->path,
            'lang' => [
                'key'   => 'activity.notifications.due',
                'attrs' => [
                   'activity' => $this->activity->title,
                   'date'     => $this->activity->due_time ?
                    Format::dateTime($this->activity->full_due_date, $this->activity->user) :
                    Format::date($this->activity->due_date, $this->activity->user),
                ],
            ],
        ];
    }
}
