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
use App\Models\Company;
use App\Innoclapps\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserAssignedToCompany as AssignedToCompanyMailable;

class UserAssignedToCompany extends Notification implements ShouldQueue
{
    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Company $company
     * @param \App\Models\User $assigneer
     *
     * @return void
     */
    public function __construct(protected Company $company, protected User $assigneer)
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
            new AssignedToCompanyMailable($this->company, $this->assigneer),
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
            'path' => $this->company->path,
            'lang' => [
                'key'   => 'company.notifications.assigned',
                'attrs' => [
                    'user' => $this->assigneer->name,
                    'name' => $this->company->display_name,
                ],
            ],
        ];
    }
}
