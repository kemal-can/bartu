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

use App\Models\Deal;
use App\Models\User;
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserAssignedToDeal as AssignedToDealMailable;

class UserAssignedToDeal extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Deal $deal
     * @param \App\Models\User $assigneer
     *
     * @return void
     */
    public function __construct(protected Deal $deal, protected User $assigneer)
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
            new AssignedToDealMailable($this->deal, $this->assigneer),
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
            'path' => $this->deal->path,
            'lang' => [
                'key'   => 'deal.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->deal->display_name,
                ],
            ],
        ];
    }
}
