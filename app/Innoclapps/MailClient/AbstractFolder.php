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

use Exception;
use Illuminate\Support\Str;
use App\Innoclapps\AbstractMask;
use App\Innoclapps\Contracts\MailClient\FolderInterface;

abstract class AbstractFolder extends AbstractMask implements FolderInterface
{
    /**
     * The folder children
     *
     * @var \App\Innoclapps\MailClient\FolderCollection|array
     */
    protected $children;

    /**
     * Try to guess folder type using known special folder names.
     *
     * @return string|null
     */
    public function getType()
    {
        $map = FolderType::KNOWN_FOLDER_NAME_MAP;

        if (array_key_exists($this->getName(), $map)) {
            return $map[$this->getName()];
        } elseif (array_key_exists($this->getId(), $map)) {
            return $map[$this->getId()];
        }

        return null;
    }

    /**
     * Check whether the folder is inbox
     *
     * @return boolean
     */
    public function isInbox()
    {
        return $this->getType() === FolderType::INBOX;
    }

    /**
     * Check whether the folder is draft
     *
     * @return boolean
     */
    public function isDraft()
    {
        return $this->getType() === FolderType::DRAFTS;
    }

    /**
     * Check whether the folder is sent
     *
     * @return boolean
     */
    public function isSent()
    {
        return $this->getType() === FolderType::SENT;
    }

    /**
     * Check whether the folder is spam
     *
     * @return boolean
     */
    public function isSpam()
    {
        return $this->getType() === FolderType::SPAM;
    }

    /**
     * Check whether the folder is trash
     *
     * @return boolean
     */
    public function isTrash()
    {
        return $this->getType() === FolderType::TRASH;
    }

    /**
     * Check whether the folder is trash or spam
     *
     * @return boolean
     */
    public function isTrashOrSpam()
    {
        return $this->isTrash() || $this->isSpam();
    }

    /**
     * Get the folder unique identiier
     *
     * @return \App\Innoclapps\MailClient\FolderIdentifier
     */
    public function identifier()
    {
        return new FolderIdentifier('id', $this->getId());
    }

    /**
      * Set children.
      *
      * @param \App\Innoclapps\MailClient\FolderCollection|array $children
      *
      * @return static
      */
    public function setChildren($children = [])
    {
        $this->children = $children;

        return $this;
    }

    /**
      * Get folder children.
      *
      * @return \App\Innoclapps\MailClient\FolderCollection|array
      */
    public function getChildren()
    {
        return $this->children ?? [];
    }

    /**
     * Check whether the folder has child folders
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
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
     * Check whether a message can be moved to this folder
     *
     * @return boolean
     */
    public function supportMove()
    {
        return $this->isSelectable();
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'remote_id'    => $this->getId(),
            'name'         => $this->getName(),
            'display_name' => $this->getDisplayName(),
            'selectable'   => $this->isSelectable(),
            'support_move' => $this->supportMove(),
            'children'     => $this->getChildren(),
            'type'         => $this->getType(),
        ];
    }

    /**
     * __get magic method
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $accessMethod = 'get' . Str::studly($name);

        if (method_exists($this, $accessMethod)) {
            return $this->{$accessMethod}();
        }

        throw new Exception("Property [{$name}] does not exist on this folder.");
    }
}
