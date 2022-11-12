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

namespace App\Innoclapps\Contracts\MailClient;

interface AttachmentInterface
{
    /**
     * Get attachment content id
     *
     * @return string|null
     */
    public function getContentId();

    /**
     * Get the attachment file name
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get the attachment content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get the attachment content type
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get the attachment size
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the attachment encoding
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Check whether the attachment is inline
     *
     * @return boolean
     */
    public function isInline();

    /**
     * Check whether the attachment is embedded message
     *
     * @return boolean
     */
    public function isEmbeddedMessage();
}
