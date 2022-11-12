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

use App\Models\UserInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Innoclapps\MailableTemplates\Placeholders\Collection;
use App\Innoclapps\MailableTemplates\Placeholders\GenericPlaceholder;

class InvitationCreated extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new message instance.
     *
     * @param \App\Models\UserInvitation $invitation
     *
     * @return void
     */
    public function __construct(public UserInvitation $invitation)
    {
    }

    /**
     * Provide the defined mailable template placeholders
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders\Collection
     */
    public function placeholders()
    {
        return new Collection([
            GenericPlaceholder::make(fn () => $this->invitation->email)->tag('email'),
            GenericPlaceholder::make(fn () => $this->invitation->link)->tag('invitation_url'),
            GenericPlaceholder::make(config('app.invitation.expires_after'))->tag('link_expires_after'),
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
        return '<p>Hi {{ email }}<br /></p>
                <p>Someone has invited you to access their CRM software.</p>
                <p><a href="{{ invitation_url }}">Click here</a> to activate your account!</p>
                <p>Please note that the link is expires after {{ link_expires_after }} days, so make sure to create the account in {{ link_expires_after }} days.</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'You have been invited to join';
    }
}
