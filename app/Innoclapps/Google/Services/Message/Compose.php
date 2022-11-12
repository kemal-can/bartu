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

namespace App\Innoclapps\Google\Services\Message;

use Google_Client;
use LogicException;
use Google_Service_Gmail;
use Illuminate\Mail\Message;
use Google_Service_Gmail_Message;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Header\Headers;
use App\Innoclapps\Mail\InteractsWithSymfonyMessage;

class Compose extends Message
{
    use InteractsWithSymfonyMessage;

    /**
     * @var \Google_Service_Gmail
     */
    protected $service;

    /**
     * Create a new message instance.
     *
     * @param \Google_Client $client
     *
     * @return void
     */
    public function __construct(protected Google_Client $client)
    {
        parent::__construct(new Email);

        $this->service = new Google_Service_Gmail($client);
    }

    /**
     * Send the created mail
     *
     * @return \App\Innoclapps\Google\Services\Messages\Mail
     */
    public function send() : Mail
    {
        $service = $this->getMessageService();

        $service->setRaw($this->createRawMessage());

        $message = $this->sendMessage($service);

        return new Mail($this->client, $message);
    }

    /**
     * Make a send message request
     *
     * @param \Google_Service_Gmail_Message $service
     *
     * @return \Google_Service_Gmail_Message
     */
    protected function sendMessage($service)
    {
        return $this->service->users_messages->send('me', $service);
    }

    /**
     * Get the message service for the Gmail request
     *
     * @return \Google_Service_Gmail_Message
     */
    protected function getMessageService()
    {
        return new Google_Service_Gmail_Message();
    }

    /**
     * Create the RAW message which is intended for the Gmail body
     * replacement of the Symfony message toString method
     *
     * We are creating our custom toString method because Symfony mailer
     * removes the BCC when converting the message to string, but we need to BCC
     * because we are using the toString method to send a message via Google services
     *
     * @see getPreparedHeaders
     *
     * @return string
     */
    protected function createRawMessage()
    {
        if (null === $body = $this->getBody()) {
            $body = new TextPart('');
        }

        return $this->base64Encode($this->getPreparedHeaders()->toString() . $body->toString());
    }

    /**
     * Get the prepared message headers
     *
     * @return \Symfony\Component\Mime\Header\Headers
     */
    public function getPreparedHeaders() : Headers
    {
        $headers = clone $this->getHeaders();

        if (! $headers->has('From')) {
            if (! $headers->has('Sender')) {
                throw new LogicException('An email must have a "From" or a "Sender" header.');
            }
            $headers->addMailboxListHeader('From', [$headers->get('Sender')->getAddress()]);
        }

        if (! $headers->has('MIME-Version')) {
            $headers->addTextHeader('MIME-Version', '1.0');
        }

        if (! $headers->has('Date')) {
            $headers->addDateHeader('Date', new \DateTimeImmutable());
        }

        // determine the "real" sender
        if (! $headers->has('Sender') && \count($froms = $headers->get('From')->getAddresses()) > 1) {
            $headers->addMailboxHeader('Sender', $froms[0]);
        }

        if (! $headers->has('Message-ID')) {
            $headers->addIdHeader('Message-ID', $this->generateMessageId());
        }

        // remove the Bcc field which should NOT be part of the sent message
        // $headers->remove('Bcc');

        return $headers;
    }

    /**
     * Prepare the Gmail message body
     *
     * @param string $data
     *
     * @return string
     */
    protected function base64Encode($data)
    {
        return rtrim(strtr(base64_encode($data), ['+' => '-', '/' => '_']), '=');
    }
}
