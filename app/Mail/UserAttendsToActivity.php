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
use App\Contracts\Attendeeable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\Resources\MailPlaceholders;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Support\MailPlaceholders\ActionButtonPlaceholder;
use App\Support\MailPlaceholders\PrivacyPolicyPlaceholder;
use App\Innoclapps\MailableTemplates\Placeholders\GenericPlaceholder;

class UserAttendsToActivity extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
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
     * Provide the defined mailable template placeholders
     *
     * @return \App\Innoclapps\Resources\MailPlaceholders
     */
    public function placeholders()
    {
        return (new MailPlaceholders('activities', $this->activity ?? null))->push([
            ActionButtonPlaceholder::make(fn () => $this->activity),
            PrivacyPolicyPlaceholder::make(),
            GenericPlaceholder::make(fn () => $this->guestable->getGuestDisplayName())
                ->tag('guest_name')
                ->description(__('activity.guest')),
            GenericPlaceholder::make(fn () => $this->guestable->getGuestEmail())
                ->tag('guest_email'),
        ])->withUrlPlaceholder();
    }

    /**
     * Build the mailable template with additional data
     *
     * @return static
     */
    public function build()
    {
        return $this->attachData($this->activity->generateICSInstance()->get(), 'invite.ics', [
            'mime' => 'text/calendar',
        ]);
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
        return '<p>Hello {{ guest_name }}<br /></p>
                <p>You have been added as a guest of the {{ title }} activity<br /></p>
                <p>{{{ action_button }}}</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'You have been added as guest to activity';
    }
}
