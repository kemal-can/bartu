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

namespace App\Innoclapps\MailableTemplates;

use Illuminate\Support\Str;
use App\Innoclapps\Html2Text;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Container\Container;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Innoclapps\Contracts\Repositories\MailableRepository;
use App\Innoclapps\MailClient\Exceptions\ConnectionErrorException;
use App\Innoclapps\Contracts\MailClient\SupportSaveToSentFolderParameter;

abstract class MailableTemplate extends Mailable
{
    /**
     * Holds the template model
     *
     * @var \App\Innoclapps\Models\MailableTemplate
     */
    protected $templateModel;

    /**
     * Provides the default mail template content
     *
     * e.q. is used when seeding the mail templates
     *
     * @return \App\Innoclapps\MailableTemplates\DefaultMailable
     */
    abstract public static function default() : DefaultMailable;

    /**
     * Send the message using the given mailer.
     *
     * @param \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer $mailer
     *
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        // Check if there is no system email account selected to send
        // mail from, in this case, use the Laravel default configuration
        if (! $systemAccountId = settings('system_email_account_id')) {
            return parent::send($mailer);
        }

        $repository = resolve(EmailAccountRepository::class);
        $account    = $repository->find($systemAccountId);

        // We will check if the email account requires authentication, as we
        // are not able to send emails if the account required authentication, in this case
        // we will return to the laravel default mailer behavior
        if (! $account->canSendMails()) {
            return parent::send($mailer);
        }

        // Call the build method in case some mailable template is overriding the method
        // to actually build the template e.q. add attachments etc..
        Container::getInstance()->call([$this, 'build']);

        $client = $account->getClient()->setFromName(
            config('app.name')
        );

        // The mailables that are sent via email account are not supposed
        // to be saved in the sent folder to takes up space, however
        // email provider like Gmail does not allow to not save the mail
        // in the sent folder, in this case, we will check if the client
        // support to avoid saving the email in the sent folder
        // otherwise we will set custom header so these emails can be excluded from syncing
        if ($client->getSmtp() instanceof SupportSaveToSentFolderParameter) {
            $client->getSmtp()->saveToSentFolder(false);
        } else {
            $client->addHeader('X-bartu-Mailable', true);
        }

        try {
            tap($client, function ($instance) {
                $views = $this->buildView();

                $instance->htmlBody($views['html']->toHtml())
                    ->textBody($views['text']->toHtml())
                    ->subject($this->getMailableTemplateRenderer()->renderSubject())
                    ->to($this->to)
                    ->cc($this->cc)
                    ->bcc($this->bcc)
                    ->replyTo($this->replyTo);
                $this->buildAttachmentsViaEmailClient($instance);
            })->send();
        } catch (ConnectionErrorException $e) {
            $repository->setRequiresAuthentication($account->id);
        }
    }

    /**
     * Get the mailable human readable name
     *
     * @return string
     */
    public static function name()
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Build the view for the message.
     *
     * @return array
     */
    protected function buildView()
    {
        $renderer = $this->getMailableTemplateRenderer();

        return array_filter([
            'html' => new HtmlString($renderer->renderHtmlLayout()),
            'text' => new HtmlString($renderer->renderTextLayout()),
        ]);
    }

    /**
     * Build the view data for the message.
     *
     * @return array
     */
    public function buildViewData()
    {
        return $this->placeholders()?->parse() ?: parent::buildViewData();
    }

    /**
     * Build the subject for the message.
     *
     * @param \Illuminate\Mail\Message $message
     *
     * @return static
     */
    protected function buildSubject($message)
    {
        $message->subject(
            $this->getMailableTemplateRenderer()->renderSubject()
        );

        return $this;
    }

    /**
     * Get the mailable template subject
     *
     * @return string|null
     */
    protected function getMailableTemplateSubject()
    {
        if ($this->subject) {
            return $this->subject;
        }

        return $this->getMailableTemplate()->getSubject() ?? $this->name();
    }

    /**
     * Get the mailable template model
     *
     * @return \App\Innoclapps\Models\MailableTemplate
     */
    public function getMailableTemplate()
    {
        if (! $this->templateModel) {
            $this->templateModel = static::templateRepository()->forMailable(
                $this,
                $this->locale ?? 'en'
            );
        }

        return $this->templateModel;
    }

    /**
     * Build the mailable attachemnts via email client
     *
     * @param \App\Innoclapps\MailClient\Client $client
     *
     * @return static
     */
    protected function buildAttachmentsViaEmailClient($client)
    {
        foreach ($this->attachments as $attachment) {
            $client->attach($attachment['file'], $attachment['options']);
        }

        foreach ($this->rawAttachments as $attachment) {
            $client->attachData(
                $attachment['data'],
                $attachment['name'],
                $attachment['options']
            );
        }

        $client->diskAttachments = $this->diskAttachments;

        return $this;
    }

    /**
     * Get the mail template repository
     *
     * @return \App\Innoclapps\Contracts\Repositories\MailableRepository
     */
    protected static function templateRepository()
    {
        return resolve(MailableRepository::class);
    }

    /**
     * Prepares alt text message from HTML
     *
     * @param string $html
     *
     * @return string
     */
    protected static function prepareTextMessageFromHtml($html)
    {
        return Html2Text::convert($html);
    }

    /**
     * Get the mail template content rendered
     *
     * @return \App\Innoclapps\MailableTemplates\Renderer
     */
    protected function getMailableTemplateRenderer() : Renderer
    {
        return app(Renderer::class, [
            'htmlTemplate' => $this->getMailableTemplate()->getHtmlTemplate(),
            'subject'      => $this->getMailableTemplateSubject(),
            'placeholders' => $this->placeholders(),
            'htmlLayout'   => $this->getHtmlLayout() ?? config('innoclapps.mailables.layout'),
            'textTemplate' => $this->getMailableTemplate()->getTextTemplate(),
            'textLayout'   => $this->getTextLayout(),
        ]);
    }

    /**
     * Get the mailable HTML layout
     *
     * @return null
     */
    public function getHtmlLayout()
    {
        return null;
    }

    /**
     * Get the mailable text layout
     *
     * @return null
     */
    public function getTextLayout()
    {
        return null;
    }

    /**
     * Provide the defined mailable template placeholders
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders\Collection|null
     */
    public function placeholders()
    {
        //
    }

    /**
     * The Mailable build method
     *
     * @see  buildSubject, buildView, send
     *
     * @return static
     */
    public function build()
    {
        return $this;
    }

    /**
     * Seed the mailable in database as mail template
     *
     * @param string $locale Locale to seed the mail template
     *
     * @return \App\Innoclapps\Models\MailableTemplate
     */
    public static function seed($locale = 'en')
    {
        $default      = static::default();
        $mailable     = get_called_class();
        $textTemplate = $default->textMessage() ?? static::prepareTextMessageFromHtml($default->htmlMessage());

        $template = static::templateRepository()->firstOrNew(
            [
                'locale'   => $locale,
                'mailable' => $mailable,
            ],
            [
                 'locale'        => $locale,
                 'mailable'      => $mailable,
                 'subject'       => $default->subject(),
                 'html_template' => $default->htmlMessage(),
                 'text_template' => $textTemplate,
            ]
        );

        return tap($template, function ($instance) use ($mailable) {
            if (! $instance->getKey()) {
                $instance->mailable = $mailable;
                $instance->name = static::name();

                $instance->save();
            }
        });
    }
}
