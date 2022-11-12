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

use App\Models\User;
use App\Models\Activity;
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserAssignedToActivity as UserAssignedToActivityMailable;

class UserAssignedToActivity extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Activity $activity
     * @param \App\Models\User $assigneer
     *
     * @return void
     */
    public function __construct(protected Activity $activity, protected User $assigneer)
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
            new UserAssignedToActivityMailable($this->activity, $this->assigneer),
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
                'key'   => 'activity.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->activity->display_name,
                ],
            ],
        ];
    }
}
