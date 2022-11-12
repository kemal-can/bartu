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
        $partStructure = $this->getEntity()->getStructure();

        if (isset($partStructure->id)) {
            return str_replace(['<', '>'], '', $partStructure->id);
        }

        return null;
    }

    /**
     * Get the attachment file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getEntity()->getFilename();
    }

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getEntity()->getContent();
    }

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getEntity()->getType() . '/' . $this->getEntity()->getSubType();
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
        $partStructure = $this->getEntity()->getStructure();

        if ($partStructure->ifdisposition) {
            return strtolower($partStructure->disposition) === 'inline';
        }

        return false;
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
        return $this->getEntity()->isEmbeddedMessage();
    }
}
