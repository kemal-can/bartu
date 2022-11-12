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

use Illuminate\Support\Collection;

trait ProvidesMailAttachments
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $attachments;

    /**
     * Check whether the message has attachments
     *
     * @return boolean
     */
    public function hasAttachments()
    {
        return ! $this->getAttachments()->isEmpty();
    }

    /**
     * Number of attachments of the message.
     *
     * @return int
     */
    public function countAttachments()
    {
        return $this->getAttachments()->count();
    }

    /**
     * Get the message attachments
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttachments()
    {
        if (! is_null($this->attachments)) {
            return $this->attachments;
        }

        $attachments = new Collection;
        $parts       = $this->getAllParts($this->parts);

        foreach ($parts as $part) {
            if (! empty($part->body->attachmentId)) {
                $attachments->push(new Attachment($this->client, $this->getId(), $part));
            }
        }

        return $this->attachments = $attachments;
    }
}
