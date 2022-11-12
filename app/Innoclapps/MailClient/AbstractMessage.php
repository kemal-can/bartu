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

use Closure;
use App\Innoclapps\AbstractMask;
use App\Innoclapps\Contracts\MailClient\MessageInterface;

abstract class AbstractMessage extends AbstractMask implements MessageInterface
{
    /**
     * Find content id's in a message body
     *
     * @param string $body
     *
     * @return array
     */
    public function findBodyContentIds($body)
    {
        // The src regex is because Gmail can add data-surl="cid:ii_k1uoz4pa1" src="cid:ii_k1uoz4pa1"
        // to the image, this happens in draft folder, when an image is directly dragged into Gmail
        // message composer area, in this case, duplicate attachments will be saved
        preg_match_all("/src=\"cid:([^'\" \]]*)/i", $body, $matches);

        // array_unique solves the issue
        return array_unique($matches[1]);
    }

    /**
     * Replace the message body content id's
     * with base64 or by provided replace callback
     *
     * @param string|null $body
     * @param string $contentId
     * @param \App\Innoclapps\Contracts\MailClient\AttachmentInterface $file
     * @param string $replaceWith
     * @param \Closure|null $replacer
     *
     * @return string
     */
    protected function replaceBodyContentIds(
        $body,
        $contentId,
        $file,
        $replaceWith,
        $replacer = null,
    ) {
        if (is_null($body)) {
            return $body;
        }

        if ($replacer && $customContent = $replacer($file)) {
            $replaceWith = '"' . $customContent . '"';
        }

        return str_replace(
            '"cid:' . str_replace(['<', '>'], '', $contentId) . '"',
            $replaceWith,
            $body
        );
    }

    /**
     * Get the message body for preview
     *
     * @param \Closure|null $replacer Provide a custom callback replace logic for the embedded images
     *
     * @return string
     */
    public function getPreviewBody(?Closure $replacer = null)
    {
        $body        = $this->getHTMLBody();
        $attachments = $this->getAttachments();

        foreach ($this->findBodyContentIds($body) as $cid) {
            foreach ($attachments as $file) {
                if ($file->getContentId() == $cid) {
                    $replace = '"data:' . $file->getContentType() . ';' . $file->getEncoding() . ',' . $file->getContent() . '"';

                    $body = $this->replaceBodyContentIds($body, $cid, $file, $replace, $replacer);
                }
            }
        }

        return $body;
    }

    /**
     * Check whether the message is bounce
     *
     * @return boolean
     */
    public function isBounce()
    {
        // Check message's From field.
        if ($from = $this->getFrom()) {
            if (preg_match('/^mailer\-daemon@/i', $from->getAddress())) {
                return true;
            }
        }

        // Detect bounce by attachment.
        $attachments = $this->getAttachments()->filter->isEmbeddedMessage();

        foreach ($attachments as $attachment) {
            if (preg_match('/delivery-status/', strtolower($attachment->getContentType()))) {
                return true;
            }
        }


        // Check Return-Path header
        if ($returnPath = $this->getHeader('return-path')) {
            if ($returnPath->getValue() === '<>') {
                return true;
            }
        }

        /**
         * Check Content-Type header.
         *
         * This is not 100% reliable, detects only standard DSN bounces.
         *
         * */
        if ($contentType = $this->getHeader('content-type')) {
            if (preg_match("/((?:[^\n]|\n[\t ])+)(?:\n[^\t ]|$)/i", $contentType->getValue(), $matches)
            && preg_match("/multipart\/report/i", $matches[1])
            && preg_match("/report-type=[\"']?delivery-status[\"']?/i", $matches[1])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Serialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'remote_id'   => $this->getId(),
            'message_id'  => $this->getMessageId(),
            'subject'     => $this->getSubject(),
            'date'        => $this->getDate(),
            'html_body'   => $this->getHtmlBody(),
            'text_body'   => $this->getTextBody(),
            'from'        => $this->getFrom(),
            'to'          => $this->getTo(),
            'cc'          => $this->getCc(),
            'bcc'         => $this->getBcc(),
            'replyTo'     => $this->getReplyTo(),
            'sender'      => $this->getSender(),
            'is_read'     => $this->isRead(),
            'is_draft'    => $this->isDraft(),
            'attachments' => $this->getAttachments(),
        ];
    }

    /**
     * Check whether the message is sent from the application
     *
     * @return boolean
     */
    public function isSentFromApplication()
    {
        return ! is_null($this->getHeader('x-bartu-app'));
    }
}
