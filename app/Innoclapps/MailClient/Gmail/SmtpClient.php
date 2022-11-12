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

namespace App\Innoclapps\MailClient\Gmail;

use App\Innoclapps\Facades\Google as Client;
use App\Innoclapps\MailClient\MasksMessages;
use App\Innoclapps\OAuth\AccessTokenProvider;
use App\Innoclapps\MailClient\FolderIdentifier;
use App\Innoclapps\MailClient\AbstractSmtpClient;
use App\Innoclapps\MailClient\Compose\PreparesSymfonyMessage;

class SmtpClient extends AbstractSmtpClient
{
    use MasksMessages,
        PreparesSymfonyMessage;

    /**
     * Create new SmtpClient instance.
     *
     * @param \App\Innoclapps\OAuth\AccessTokenProvider $token
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Client::connectUsing($token);
    }

    /**
     * Send mail message
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function send()
    {
        $message = $this->prepareSymfonyMessage(
            Client::message()->sendMail(),
            $this->token->getEmail()
        );

        return $this->maskMessage($message->send()->load(), Message::class);
    }

    /**
     * Reply to a given mail message
     *
     * @param string $remoteId
     * @param null|\App\Innoclapps\MailClient\FolderIdentifier $folder
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function reply($remoteId, ?FolderIdentifier $folder = null)
    {
        /** @var \App\Innoclapps\MailClient\Gmail\Message **/
        $remoteMessage = $this->imap->getMessage($remoteId);

        $message = $this->prepareSymfonyMessage($remoteMessage->reply(), $this->token->getEmail());

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
            $message->subject($this->createReplySubject($remoteMessage->getSubject()));
        }

        return $this->maskMessage($message->send()->load(), Message::class);
    }

    /**
     * Forward the given message
     *
     * @param int $remoteId
     * @param null|\App\Innoclapps\MailClient\FolderIdentifier $folder
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function forward($remoteId, ?FolderIdentifier $folder = null)
    {
        /** @var \App\Innoclapps\MailClient\Gmail\Message **/
        $remoteMessage = $this->imap->getMessage($remoteId);

        $message = $this->prepareSymfonyMessage($remoteMessage->reply(), $this->token->getEmail());

        /*
        $inlineMessage = $this->inlineMessage(
            $remoteMessage,
            $this->createInlineImagesProcessingFunction($message)
        );

        $message->setBody($message->getBody() . $inlineMessage, $this->getContentType());
        */

        // When there is no subject set, we will just
        // create a reply subject from the original message
        if (! $this->subject) {
            $message->subject($this->createForwardSubject($remoteMessage->getSubject()));
        }

        return $this->maskMessage($message->send()->load(), Message::class);
    }
}
