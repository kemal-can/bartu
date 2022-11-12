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

use App\Innoclapps\MailClient\AbstractAttachment;

class Attachment extends AbstractAttachment
{
    /**
     * Hold the attachment cached content
     *
     * @var string|null
     */
    protected $content;

    /**
     * Get attachment content id
     *
     * @return string|null
     */
    public function getContentId()
    {
        return $this->getEntity()->getContentId();
    }

    /**
     * Get the attachment file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getEntity()->getFileName();
    }

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent()
    {
        // Prevents making multiple API requests to get the content
        if ($this->content) {
            return $this->content;
        }

        return $this->content = $this->getEntity()->getContent();
    }

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getEntity()->getMimeType();
    }

    /**
     * Get the attachment encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->getEntity()->getEncoding();
    }

    /**
     * Check whether the attachment is inline
     *
     * @return boolean
     */
    public function isInline()
    {
        return $this->getEntity()->isInline();
    }

    /**
     * Get the attachment size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getEntity()->getSize();
    }

    /**
    * Check whether the attachment is embedded message
    *
    * @return boolean
    */
    public function isEmbeddedMessage()
    {
        return $this->getContentType() == 'message/rfc822';
    }
}
