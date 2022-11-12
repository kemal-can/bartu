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

use App\Innoclapps\AbstractMask;
use App\Innoclapps\Contracts\MailClient\AttachmentInterface;

abstract class AbstractAttachment extends AbstractMask implements AttachmentInterface
{
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
            'file_name'    => $this->getFileName(),
            'content'      => $this->getContent(),
            'content_type' => $this->getContentType(),
            'encoding'     => $this->getEncoding(),
            'content_id'   => $this->getContentId(),
            'size'         => $this->getSize(),
            'inline'       => $this->isInline(),
        ];
    }
}
