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
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserMentioned as UserMentionedMailable;

class UserMentioned extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param string $mentionUrl
     * @param \App\Models\User $mentioner
     *
     * @return void
     */
    public function __construct(public string $mentionUrl, public User $mentioner)
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
            new UserMentionedMailable(
                $notifiable,
                $this->mentionUrl,
                $this->mentioner
            ),
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
            'path' => $this->mentionUrl,
            'lang' => [
                'key'   => 'notifications.user_mentioned',
                'attrs' => [
                    'name' => $this->mentioner->name,
                ],
            ],
        ];
    }
}
