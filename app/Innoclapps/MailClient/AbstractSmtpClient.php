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

namespace App\Innoclapps\MailClient;

use Traversable;
use Pelago\Emogrifier\CssInliner;
use Egulias\EmailValidator\EmailValidator;
use Illuminate\Contracts\Support\Arrayable;
use Egulias\EmailValidator\Validation\RFCValidation;
use App\Innoclapps\Contracts\MailClient\SmtpInterface;

abstract class AbstractSmtpClient implements SmtpInterface
{
    use Smtpable;

    /**
     * The SMTP client may need to the IMAP client e.q. to fetch a message(s)
     *
     * @var \App\Innoclapps\Contracts\MailClient\ImapInterface|\App\Innoclapps\MailClient\AbstractImapClient
     */
    protected $imap;

    /**
     * The "subject" information for the message.
     *
     * @var string
     */
    protected $subject;

    /**
     * The message reply/send HTML body
     *
     * @var string|null
     */
    protected $htmlBody;

    /**
     * The message reply/send TEXT body
     *
     * @var string|null
     */
    protected $textBody;

    /**
     * The "recipients" for the message (to).
     *
     * @var array
     */
    protected $to = [];

    /**
     * The "cc" information for the message.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The "bcc" information for the message.
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * The "reply-to" information for the message.
     *
     * @var array
     */
    protected $replyTo = [];

    /**
     * The attachments for the message.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * The raw attachments for the message.
     *
     * @var array
     */
    protected $rawAttachments = [];

    const CONTENT_TYPE_HTML = 'text/html';

    const CONTENT_TYPE_TEXT = 'text/plain';

    /**
     * The message custom header
     *
     * @var array
     */
    protected $headers = [
        [
            'name'  => 'X-bartu-App',
            'value' => 'true',
        ],
    ];

    /**
     * Get the mail content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->htmlBody ? static::CONTENT_TYPE_HTML : static::CONTENT_TYPE_TEXT;
    }

    /**
     * Check whether the mail is HTML content type
     *
     * @return boolean
     */
    public function isHtmlContentType()
    {
        return $this->getContentType() === static::CONTENT_TYPE_HTML;
    }

    /**
     * Check whether the mail is Text content type
     *
     * @return boolean
     */
    public function isTextContentType()
    {
        return $this->getContentType() === static::CONTENT_TYPE_TEXT;
    }

    /**
     * Set mail message subject
     *
     * @param string $subject
     *
     * @return static
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set mail message HTML body
     *
     * @param string $body
     *
     * @return static
     */
    public function htmlBody($body)
    {
        $this->htmlBody = $body;

        return $this;
    }

    /**
     * Set mail message TEXT body
     *
     * @param string $body
     *
     * @return static
     */
    public function textBody($body)
    {
        $this->textBody = $body;

        return $this;
    }

    /**
     * Set the mail recipients
     *
     * @param array|string $address
     * @param string|null $name
     *
     * @return static
     */
    public function to($address, $name = null)
    {
        return $this->addAddress($address, $name, 'to');
    }

    /**
     * Set the cc address for the mail message.
     *
     * @param array|string $address
     * @param string|null $name
     *
     * @return static
     */
    public function cc($address, $name = null)
    {
        return $this->addAddress($address, $name, 'cc');
    }

    /**
     * Set the bcc address for the mail message.
     *
     * @param array|string $address
     * @param string|null $name
     *
     * @return static
     */
    public function bcc($address, $name = null)
    {
        return $this->addAddress($address, $name, 'bcc');
    }

    /**
     * Set the replyTo address for the mail message.
     *
     * @param array|string $address
     * @param string|null $name
     *
     * @return static
     */
    public function replyTo($address, $name = null)
    {
        return $this->addAddress($address, $name, 'replyTo');
    }

    /**
     * Attach a file to the message.
     *
     * @param string $file
     * @param array $options
     *
     * @return static
     */
    public function attach($file, array $options = [])
    {
        $this->attachments[] = compact('file', 'options');

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param string $data
     * @param string $name
     * @param array $options
     *
     * @return static
     */
    public function attachData($data, $name, array $options = [])
    {
        $this->rawAttachments[] = compact('data', 'name', 'options');

        return $this;
    }

    /**
     * Add email message custom headers
     *
     * @param string $name
     * @param string $value
     *
     * @return static
     */
    public function addHeader(string $name, string $value)
    {
        $this->headers[] = [
            'name'  => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Sets the imap client related to this SMTP client
     *
     * @param \App\Innoclapps\Contracts\MailClient\ImapInterface $client
     *
     * @return static
     */
    public function setImapClient($client)
    {
        $this->imap = $client;

        return $this;
    }

    /**
     * Create reply subject with system special reply prefix
     *
     * @param string $subject
     *
     * @return string
     */
    public function createReplySubject($subject)
    {
        return config('innoclapps.mail_client.reply_prefix') . trim(
            preg_replace($this->cleanupSubjectSearch(), '', $subject)
        );
    }

    /**
     * Create forward subject with system special forward prefix
     *
     * @param string $subject
     *
     * @return string
     */
    public function createForwardSubject($subject)
    {
        return config('innoclapps.mail_client.forward_prefix') . trim(
            preg_replace($this->cleanupSubjectSearch(), '', $subject)
        );
    }

    /**
     * Get the clean up subject search regex
     *
     * @link https://en.wikipedia.org/wiki/List_of_email_subject_abbreviations
     *
     * @return array
     */
    protected function cleanupSubjectSearch()
    {
        return [
            // Re
            '/RE\:/i', '/SV\:/i', '/Antw\:/i', '/VS\:/i', '/RE\:/i',
            '/REF\:/i', '/ΑΠ\:/i', '/ΣΧΕΤ\:/i', '/Vá\:/i', '/R\:/i',
            '/RIF\:/i', '/BLS\:/i', '/RES\:/i', '/Odp\:/i', '/YNT\:/i',
            '/ATB\:/i',
            // FW
            '/FW\:/i', '/FWD\:/i',
            '/Doorst\:/i', '/VL\:/i', '/TR\:/i', '/WG\:/i', '/ΠΡΘ\:/i',
            '/Továbbítás\:/i', '/I\:/i', '/FS\:/i', '/TRS\:/i', '/VB\:/i',
            '/RV\:/i', '/ENC\:/i', '/PD\:/i', '/İLT\:/i', '/YML\:/i',
        ];
    }

    /**
     * Create inline version of the given message
     *
     * @param \App\Innoclapps\Contracts\MailClient\MessageInterface $message Previous message
     * @param \Closure $callback
     *
     * @return string
     */
    protected function inlineMessage($message, $callback)
    {
        // Let's try to include the messages inline attachments
        // If the message is composed with text only, the html body may be empty
        // We won't need any replacements, will use just the text body
        $body = $message->getHtmlBody() ?
            // The callback should return either the new contentid of the inline attachment or return the data in base64
            // e.q. "data:image/jpeg;base64,...."  or any custom logic e.q. /media file path when storing the attachment
            $message->getPreviewBody($callback) :
            $message->getTextBody();

        // Maybe the message was empty?
        if (empty($body)) {
            return $body;
        }

        return CssInliner::fromHtml($body)
            ->inlineCss()
            ->renderBodyContent();
    }

    /**
     * Create reply body with quoted message
     *
     * @param \App\Innoclapps\Contracts\MailClient\MessageInterface $message Previous message
     * @param \Closure $callback
     *
     * @return string|null
     */
    public function createQuoteOfPreviousMessage($message, $callback)
    {
        $date = $message->getDate();
        $from = htmlentities('<') . $message->getFrom()->getAddress() . htmlentities('>');

        if ($name = $message->getFrom()->getPersonName()) {
            $from = $name . ' ' . $from;
        }

        $wroteText = 'On ' . $date->format('D, M j, Y') . ', at ' . $date->format('g:i A') . ' ' . $from . ' wrote:';
        $quote     = $this->inlineMessage($message, $callback);

        // Maybe the message was empty?
        if (empty($quote)) {
            return $quote;
        }

        // 2 new lines allow the EmailReplyParser to properly determine the actual reply message
        return "\n\n" . $wroteText . "\n" . "<blockquote class=\"bartu_quote\">$quote</blockquote>";
    }

    /**
     * Add address
     *
     * @param string|array $address
     * @param string $name
     * @param string $property
     *
     * @return static
     */
    protected function addAddress($address, $name, $property)
    {
        $this->{$property} = array_merge(
            $this->{$property},
            $this->parseAddresses($this->arrayOfAddresses($address) ? $address : [$address => $name])
        );

        return $this;
    }

    /**
     * Parse the multi-address array into the necessary format.
     *
     * ->to('some1@address.tld')
     *
     * ->to(['some3@address.tld' => 'The Name']);
     *
     * ->to(['some2@address.tld']);
     *
     * ->to(['some4@address.tld', 'other4@address.tld']);
     *
     * ->to([
     *       'recipient-with-name@address.ltd' => 'Recipient Name One',
     *       'no-name@address.ltd',
     *       'named-recipient@address.ltd' => 'Recipient Name Two',
     *  ]);
     *
     * ->to(['name' => 'Name', 'address' => 'example@address.ltd']);
     *
     * ->to([
     *     ['name' => 'Name', 'address' => 'example@address.ltd'],
     *     ['name' => 'Name', 'address' => 'example@address.ltd']
     * ]);
     *
     * ->to([['name' => 'Name', 'address' => 'example@address.ltd'], 'example@address.ltd']);
     *
     * ->to([['address' => 'example@address.ltd']]);
     *
     * ->to([
     *      ['name' => 'Name', 'address' => 'example@address.ltd'],
     *      'example@address.ltd',
     *      ['address' => 'example@address.ltd']
     * ]);
     *
     * @param array $value
     *
     * @return array
     */
    protected function parseAddresses($value)
    {
        $addresses = collect([]);

        if (count($value) === 2 && isset($value['address'])) {
            $addresses->push(['name' => $value['name'] ?? null, 'address' => $value['address']]);
        } else {
            foreach ($value as $address => $values) {
                if (! is_array($values)) {
                    if (is_numeric($address)) {
                        $addresses->push(['name' => null, 'address' => $values]);
                    } elseif (is_null($values)) {
                        $addresses->push(['name' => null, 'address' => $address]);
                    } else {
                        $addresses->push(['name' => $values, 'address' => $address]);
                    }
                } else {
                    $addresses = $addresses->merge([[
                        'name'    => $values['name'] ?? null,
                        'address' => $values['address'],
                    ]]);
                }
            }
        }

        return $addresses->filter(function ($recipient) {
            return (new EmailValidator)->isValid($recipient['address'], new RFCValidation());
        })->map(function ($recipient) {
            // Make sure that the recipient name is always null
            // even when passed as empty string
            $recipient['name'] = $recipient['name'] === '' ? null : $recipient['name'];

            return $recipient;
        })->values()->all();
    }

    /**
     * Determine if the given "address" is actually an array of addresses.
     *
     * @param mixed $address
     *
     * @return boolean
     */
    protected function arrayOfAddresses($address)
    {
        return is_array($address) ||
               $address instanceof Arrayable ||
               $address instanceof Traversable;
    }
}
