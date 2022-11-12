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
use App\Models\Contact;
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserAssignedToContact as AssignedToContactMailable;

class UserAssignedToContact extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Contact $contact
     * @param \App\Models\User $assigneer
     *
     * @return void
     */
    public function __construct(protected Contact $contact, protected User $assigneer)
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
            new AssignedToContactMailable($this->contact, $this->assigneer),
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
            'path' => $this->contact->path,
            'lang' => [
                'key'   => 'contact.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->contact->display_name,
                ],
            ],
        ];
    }
}
