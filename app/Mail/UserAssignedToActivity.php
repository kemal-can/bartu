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

namespace App\Mail;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\Resources\MailPlaceholders;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Support\MailPlaceholders\ActionButtonPlaceholder;
use App\Support\MailPlaceholders\PrivacyPolicyPlaceholder;
use App\Innoclapps\MailableTemplates\Placeholders\UserPlaceholder;

class UserAssignedToActivity extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
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
     * Provide the defined mailable template placeholders
     *
     * @return \App\Innoclapps\Resources\MailPlaceholders
     */
    public function placeholders()
    {
        return (new MailPlaceholders('activities', $this->activity ?? null))->push([
            ActionButtonPlaceholder::make(fn () => $this->activity),
            PrivacyPolicyPlaceholder::make(),
            UserPlaceholder::make(fn () => $this->assigneer->name)
                ->tag('assigneer')
                ->description(__('activity.mail_placeholders.assigneer')),
        ])->withUrlPlaceholder();
    }

    /**
     * Provides the mail template default configuration
     *
     * @return \App\Innoclapps\MailableTemplates\DefaultMailable
     */
    public static function default() : DefaultMailable
    {
        return new DefaultMailable(static::defaultHtmlTemplate(), static::defaultSubject());
    }

    /**
     * Provides the mail template default message
     *
     * @return string
     */
    public static function defaultHtmlTemplate()
    {
        return '<p>Hello {{ user }}<br /></p>
                <p>You have been assigned to activity {{ name }} by {{ assigneer }}<br /></p>
                <p>{{{ action_button }}}</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'You are added as an owner of the activity {{ title }}';
    }
}
