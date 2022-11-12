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
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Support\MailPlaceholders\ActionButtonPlaceholder;
use App\Support\MailPlaceholders\PrivacyPolicyPlaceholder;
use App\Innoclapps\MailableTemplates\Placeholders\Collection;
use App\Innoclapps\MailableTemplates\Placeholders\UrlPlaceholder;
use App\Innoclapps\MailableTemplates\Placeholders\UserPlaceholder;

class UserMentioned extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new mailable template instance.
     *
     * @param \App\Models\User $mentioned
     * @param string $mentionUrl
     * @param \App\Models\User $mentioner
     *
     * @return void
     */
    public function __construct(protected User $mentioned, protected string $mentionUrl, protected User $mentioner)
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
                UserPlaceholder::make(fn () => $this->mentioned->name)
                    ->tag('mentioned_user')
                    ->description(__('mail_template.placeholders.mentioned_user')),

                UserPlaceholder::make(fn () => $this->mentioner->name)
                    ->description(__('mail_template.placeholders.user_that_mentions')),

            UrlPlaceholder::make(fn () => $this->mentionUrl)
                ->description(__('mail_template.placeholders.mention_url')),

            ActionButtonPlaceholder::make(fn () => $this->mentionUrl),

            PrivacyPolicyPlaceholder::make(),
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
        return '<p>Hello {{ mentioned_user }}<br /></p>
                <p>{{ user }} mentioned you.<br /></p>
                <p>{{{ action_button }}}<br /></p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'You Were Mentioned by {{ user }}';
    }
}
