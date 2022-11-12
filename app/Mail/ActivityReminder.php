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

use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\Resources\MailPlaceholders;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Support\MailPlaceholders\ActionButtonPlaceholder;
use App\Support\MailPlaceholders\PrivacyPolicyPlaceholder;

class ActivityReminder extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable instance.
     *
     * @param \App\Models\Activity $activity
     *
     * @return void
     */
    public function __construct(protected Activity $activity)
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
        return '<p>Hello {{ assigned }}<br /></p>
                <p>Your {{ title }} activity is due on {{ due_date }}<br /></p>
                <p>{{{ action_button }}}</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'Your {{ title }} activity is due on {{ due_date }}';
    }
}
