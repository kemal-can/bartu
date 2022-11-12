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

use App\Models\WebForm;
use App\Support\WebForm\FormSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Innoclapps\MailableTemplates\Placeholders\Collection;
use App\Innoclapps\MailableTemplates\Placeholders\GenericPlaceholder;

class WebFormSubmitted extends MailableTemplate implements ShouldQueue
{
    /**
     * Create a new message instance.
     *
     * @param \App\Models\WebForm $form
     * @param \App\Support\WebForm\FormSubmission $submission
     *
     * @return void
     */
    public function __construct(public WebForm $form, public FormSubmission $submission)
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
            GenericPlaceholder::make(fn () => $this->form->title)
                ->tag('title'),
            GenericPlaceholder::make(fn () => (string) $this->submission)
                ->withStartInterpolation('{{{')
                ->withEndInterpolation('}}}')->tag('payload'),
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
        return '<p>There is new submission via the {{ title }} web form.<br /><br /></p>
                <p>{{{ payload }}}</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'New submission on {{ title }} form';
    }
}
