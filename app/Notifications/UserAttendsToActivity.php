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
use App\Contracts\Attendeeable;
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserAttendsToActivity as UserAttendsToActivityMailable;

class UserAttendsToActivity extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Contracts\Attendeeable $guestable
     * @param \App\Models\Activity $activity
     *
     * @return void
     */
    public function __construct(protected Attendeeable $guestable, protected Activity $activity)
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
            new UserAttendsToActivityMailable($this->guestable, $this->activity),
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
                'key'   => 'activity.notifications.added_as_guest',
                'attrs' => [],
            ],
        ];
    }
}
