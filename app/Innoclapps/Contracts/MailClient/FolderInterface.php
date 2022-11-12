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

interface FolderInterface
{
    /**
     * Get the folder unique remote id identifier
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Get folder messages
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessages();

    /**
     * Get messages starting from specific date and time
     *
     * @param string $dateTime
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessagesFrom($dateTime);

    /**
     * Get folder message
     *
     * @throws \App\Innoclapps\MailClient\Exceptions\MessageNotFoundException
     *
     * @param mixed $uid
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function getMessage($uid);

    /**
     * Get the folder system name
     *
     * @return string
     */
    public function getName();

    /**
     * Get the folder display name
     *
     * @return string
     */
    public function getDisplayName();

    /**
      * Set folder children.
      *
      * @param \App\Innoclapps\MailClient\FolderCollection|array $children
      *
      * @return static
      */
    public function setChildren($children = []);

    /**
      * Get folder children.
      *
      * @return \App\Innoclapps\MailClient\FolderCollection|array
      */
    public function getChildren();

    /**
      * Check whether the folder has child folders
      *
      * @return boolean
      */
    public function hasChildren();

    /**
    * Check whether the folder is selectable
    *
    * @return boolean
    */
    public function isSelectable();

    /**
     * Check whether a message can be moved to this folder
     *
     * @return boolean
     */
    public function supportMove();

    /**
     * Get the folder type
     * e.q. sent, drafts, inbox, trash
     *
     * @return string
     */
    public function getType();

    /**
     * Get the folder unique identiier
     *
     * @return \App\Innoclapps\MailClient\FolderIdentifier
     */
    public function identifier();

    /**
     * Check whether the folder is inbox
     *
     * @return boolean
     */
    public function isInbox();

    /**
     * Check whether the folder is draft
     *
     * @return boolean
     */
    public function isDraft();

    /**
     * Check whether the folder is sent
     *
     * @return boolean
     */
    public function isSent();

    /**
     * Check whether the folder is spam
     *
     * @return boolean
     */
    public function isSpam();

    /**
     * Check whether the folder is trash
     *
     * @return boolean
     */
    public function isTrash();

    /**
     * Check whether the folder is trash or spam
     *
     * @return boolean
     */
    public function isTrashOrSpam();
}
