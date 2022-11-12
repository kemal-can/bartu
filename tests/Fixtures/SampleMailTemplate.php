<?php

namespace Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Innoclapps\MailableTemplates\DefaultMailable;
use App\Innoclapps\MailableTemplates\MailableTemplate;
use App\Innoclapps\MailableTemplates\Placeholders\Collection;

class SampleMailTemplate extends MailableTemplate
{
    use Queueable, SerializesModels;

    /**
     * The mailable variables/placeholders
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders
     */
    public function placeholders()
    {
        return new Collection([]);
    }

    /**
     * Provides the mail template default configuration
     *
     * @return DefaultMailable
     */
    public static function default() : DefaultMailable
    {
        return new DefaultMailable(static::defaultHtmlTemplate(), static::defaultSubject(), static::defaultTextMessage());
    }

    /**
     * Provides the mail template default message
     *
     * @return string
     */
    public static function defaultHtmlTemplate()
    {
        return 'Sample message';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'Sample subject';
    }

    /**
     * Provides the mail template default text message
     *
     * @return string
     */
    public static function defaultTextMessage()
    {
        return 'Sample text message';
    }

    /**
     * Get the mailable human readable name
     *
     * @return string
     */
    public static function name()
    {
        return 'Sample template';
    }
}
