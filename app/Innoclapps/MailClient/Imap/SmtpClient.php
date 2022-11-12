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

namespace App\Innoclapps\MailClient\Imap;

use Illuminate\Mail\Mailer;
use Symfony\Component\Mailer\Transport\Dsn;
use App\Innoclapps\MailClient\FolderIdentifier;
use App\Innoclapps\MailClient\AbstractSmtpClient;
use App\Innoclapps\Contracts\MailClient\Connectable;
use App\Innoclapps\MailClient\Compose\PreparesSymfonyMessage;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use App\Innoclapps\Contracts\MailClient\SupportSaveToSentFolderParameter;

class SmtpClient extends AbstractSmtpClient implements Connectable, SupportSaveToSentFolderParameter
{
    use PreparesSymfonyMessage;

    /**
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * Indicates whether to sent the message in the sent folder
     *
     * @var boolean
     */
    protected $saveToSentFolder = true;

    /**
     * Create new SmtpClient instance.
     *
     * @param \App\Innoclapps\MailClient\Imap\SmtpConfig $config
     */
    public function __construct(protected SmtpConfig $config)
    {
    }

    /**
     * Get the mailer
     *
     * @return Illuminate\Mail\Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Send mail message
     *
     * @see  https://github.com/laravel/framework/issues/10235
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function send()
    {
        $this->ensureConfigured();

        $message = $this->mailer->send([], [], function ($message) {
            $this->prepareSymfonyMessage($message, $this->getConfig()->email());
        });

        return $this->handleSentMessage($message, $this->subject);
    }

    /**
     * Reply to a given mail message
     *
     * @param string $remoteId
     * @param \App\Innoclapps\MailClient\FolderIdentifier $folder
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function reply($remoteId, ?FolderIdentifier $folder = null)
    {
        $this->ensureConfigured();

        // Subject for comparing the sent message
        $subject = $this->subject;

        $message = $this->mailer->send([], [], function ($message) use ($remoteId, $folder, &$subject) {
            $remoteMessage = $this->imap->getFolder($folder->value)->getMessage($remoteId);
            $message = $this->prepareSymfonyMessage($message, $this->getConfig()->email());

            /*
            $quote = $this->createQuoteOfPreviousMessage(
                $remoteMessage,
                $this->createInlineImagesProcessingFunction($message)
            );

            $message->setBody($message->getBody() . $quote, $this->getContentType());
            */

            // When there is no subject set, we will just
            // create a reply subject from the original message
            if (! $this->subject) {
                $message->subject($subject = $this->createReplySubject($remoteMessage->getSubject()));
            }

            $references = $remoteMessage->getReferences();

            if ($messageId = $remoteMessage->getMessageId()) {
                $references[] = $messageId;
                $this->addSymfonyMessageInReplyToHeader($message->getSymfonyMessage(), $messageId);
            }

            $this->addSymfonyMessageReferencesHeader($message->getSymfonyMessage(), $references);
        });

        return $this->handleSentMessage($message, $subject);
    }

    /**
     * Forward the given mail message
     *
     * @param string $remoteId
     * @param \App\Innoclapps\MailClient\FolderIdentifier $folder
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function forward($remoteId, ?FolderIdentifier $folder = null)
    {
        $this->ensureConfigured();

        // Subject for comparing the sent message
        $subject = $this->subject;

        $message = $this->mailer->send([], [], function ($message) use ($remoteId, $folder, &$subject) {
            $remoteMessage = $this->imap->getFolder($folder->value)->getMessage($remoteId);
            $message = $this->prepareSymfonyMessage($message, $this->getConfig()->email());

            /*
            $inline = $this->inlineMessage(
                $remoteMessage,
                $this->createInlineImagesProcessingFunction($message)
            );

            $message->setBody($message->getBody() . $inline, $this->getContentType());
            */

            // When there is no subject set, we will just
            // create a reply subject from the original message
            if (! $this->subject) {
                $message->subject($subject = $this->createForwardSubject($remoteMessage->getSubject()));
            }

            $references = $remoteMessage->getReferences();

            if ($messageId = $remoteMessage->getMessageId()) {
                $references[] = $messageId;
                $this->addSymfonyMessageInReplyToHeader($message->getSymfonyMessage(), $messageId);
            }

            $this->addSymfonyMessageReferencesHeader($message->getSymfonyMessage(), $references);
        });

        return $this->handleSentMessage($message, $subject);
    }

    /**
     * Connect to SMTP server
     *
     * @return \Illuminate\Mail\Mailer
     */
    public function connect()
    {
        $encryption = $this->getConfig()->encryption() ?: '';
        $username   = $this->getConfig()->username() ?? $this->getConfig()->email();

        $factory = new EsmtpTransportFactory;

        $transport = $factory->create(new Dsn(
            ! empty($encryption) && $encryption === 'tls' ? (($this->getConfig()->port() == 465) ? 'smtps' : 'smtp') : '',
            $this->getConfig()->host(),
            $username,
            $this->getConfig()->password(),
            $this->getConfig()->port()
        ));

        return $this->mailer = new Mailer('smtp', app()->get('view'), $transport, app()->get('events'));
    }

    /**
     * Test the SmtpClient client connection
     *
     * @return void
     */
    public function testConnection()
    {
        $this->ensureConfigured();

        $this->mailer->raw('Test', function ($message) {
            $this->prepareSymfonyMessage($message, $this->getConfig()->email())
                ->to($this->getConfig()->email())
                ->subject('Test Account Configuration');
        });
    }

    /**
     * Get the SMTP configuration
     *
     * @return \App\Innoclapps\MailClient\Imap\SmtpConfig
     */
    public function getConfig() : SmtpConfig
    {
        return $this->config;
    }

    /**
     * Indicates whether the message should be saved
     * to the sent folder after it's sent
     *
     * In most cases, this is valid for new mails not for replies
     *
     * @param boolean $value
     *
     * @return static
     */
    public function saveToSentFolder($value)
    {
        $this->saveToSentFolder = $value;

        return $this;
    }

    /**
     * Ensure the SMTP client is configured
     *
     * @return void
     */
    public function ensureConfigured()
    {
        if ($this->mailer) {
            return;
        }

        $this->connect();
    }

    /**
     * Save the sent message in the sent folder and retrieve
     *
     * @param \Illuminate\Mail\SentMessage $message
     * @param string $subject
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface|null
     */
    protected function handleSentMessage($message, $subject)
    {
        if (! $this->saveToSentFolder) {
            return null;
        }

        /** @var \App\Innoclapps\MailClient\Imap\ImapClient **/
        $imap = $this->imap;

        $imap->addMessageToSentFolder($message->toString());

        return $imap->getLatestSentMessageAndStrictCompare(
            $subject,
            $this->config->email(),
            $this->to,
            $message->getMessageId()
        );
    }
}
