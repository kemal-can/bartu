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

namespace App\Innoclapps\MailClient\Outlook;

use App\Innoclapps\MailClient\AbstractAttachment;

class Attachment extends AbstractAttachment
{
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
        return $this->getEntity()->getName();
    }

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getEntity()->getContentBytes();
    }

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getEntity()->getContentType();
    }

    /**
     * Get the attachment encoding
     *
     * All Microsoft attachments encoding are base64
     *
     * @return string
     */
    public function getEncoding()
    {
        return 'base64';
    }

    /**
     * Check whether the attachment is inline
     *
     * @return boolean
     */
    public function isInline()
    {
        return $this->getEntity()->getIsInline();
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
        return $this->getEntity()->getContentType() == 'message/rfc822';
    }
}
