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

use DateTime;
use DateInterval;
use DateTimeImmutable;
use Ddeboer\Imap\Search\Flag\Seen;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Flag\Unseen;
use App\Innoclapps\MailClient\MasksMessages;
use App\Innoclapps\MailClient\AbstractFolder;
use App\Innoclapps\MailClient\FolderIdentifier;
use Ddeboer\Imap\Search\Date\Since as SinceFilter;
use Ddeboer\Imap\Exception\MessageDoesNotExistException;
use App\Innoclapps\MailClient\Exceptions\MessageNotFoundException;

class Folder extends AbstractFolder
{
    use MasksMessages;

    /**
     * Get folder uidvalidity
     *
     * NOTE: Do not rely on this value as on some mail servers, for all folders it's the same value
     * In this case, the folder name should be used instead.
     *
     * @return int|null
     */
    public function getId()
    {
        if ($this->isSelectable() &&
            $imapUidValidity = $this->getEntity()->getStatus(SA_UIDVALIDITY)) {
            // Check if the uidvalidity property is set, as e.q. on Outlook this property may not be present
            return $imapUidValidity->uidvalidity ?? null;
        }

        return null;
    }

    /**
     * Get folder messages
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessages(...$args)
    {
        return $this->maskMessages(
            $this->getEntity()->getMessages(...$args),
            Message::class
        );
    }

    /**
     * Get messages starting from specific date and time
     *
     * @param string $dateTime
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessagesFrom($dateTime)
    {
        return $this->getMessages(
            $this->createSinceFilter($dateTime),
            \SORTDATE, // Sort criteria
            true // Descending order
        );
    }

    /**
     * Get the latest message from a folder
     *
     * @return \App\Innoclapps\MailClient\Imap\Message|null
     */
    public function getLatestMessage()
    {
        $today          = new DateTimeImmutable();
        $fiveMinutesAgo = $today->sub(new DateInterval('PT5M'));

        $messages = $this->getMessages(
            $this->createSinceFilter($fiveMinutesAgo),
            \SORTDATE, // Sort criteria
            true // Descending order
        );

        return $messages->first();
    }

    /**
     * Get folder message
     *
     * @throws \App\Innoclapps\MailClient\Exceptions\MessageNotFoundException
     *
     * @param int $uid
     *
     * @return \App\Innoclapps\MailClient\Imap\Message
     */
    public function getMessage($uid)
    {
        try {
            return $this->maskMessage(
                $this->getEntity()->getMessage($uid),
                Message::class
            );
        } catch (MessageDoesNotExistException $e) {
            throw new MessageNotFoundException;
        }
    }

    /**
     * Add a message to the mailbox.
     *
     * @param string $message
     * @param null|string $options
     *
     * @return bool
     */
    public function addMessage(string $message, string $options = null)
    {
        return $this->getEntity()->addMessage($message, $options);
    }

    /**
     * Get the folder system name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getEntity()->getName();
    }

    /**
     * Get the folder display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        if (empty($this->getDelimiter())) {
            return $this->getEntity()->getName();
        }

        return last(explode(
            $this->getDelimiter(),
            $this->getEntity()->getName()
        ));
    }

    /**
     * Get the folder delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->getEntity()->getDelimiter();
    }

    /**
     * Check whether the folder is selectable
     *
     * @return boolean
     */
    public function isSelectable()
    {
        return ! ($this->getEntity()->getAttributes() & \LATT_NOSELECT);
    }

    /**
     * Get messages since last uid scanned
     *
     * @param int $uid
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessagesSinceLastUid($uid)
    {
        $messages = $this->getEntity()->getMessageSequence(++$uid . ':*');

        return $this->maskMessages($messages, Message::class);
    }

    /**
     * Get all unseen message UID's
     *
     * @param string|null $since
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUnseenIds($since = null)
    {
        $search = new SearchExpression();
        $search->addCondition(new Unseen);

        if ($since) {
            $this->addSinceSearchExpression($search, $since);
        }

        /**
         * We will use the \Ddeboer\Imap\MessageIterator Arrayiterator method
         * getArrayCopy to get the Id's only
         *
         * As if we loop throught the messages the package will
         * lazy load the messages structure
         */
        return collect($this->getEntity()->getMessages($search)->getArrayCopy());
    }

    /**
     * Get all seen message UID's
     *
     * @param string|null $since
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSeenIds($since = null)
    {
        $search = new SearchExpression();
        $search->addCondition(new Seen);

        if ($since) {
            $this->addSinceSearchExpression($search, $since);
        }

        /**
         * We will use the \Ddeboer\Imap\MessageIterator Arrayiterator method
         * getArrayCopy to get the Id's only
         *
         * As if we loop throught the messages the package will
         * lazy load the messages structure
         */
        return collect($this->getEntity()->getMessages($search)->getArrayCopy());
    }

    /**
     * Get the last uid from the folder
     *
     * @return int
     */
    public function getLastUid()
    {
        $next = $this->getNextUid();

        // No messages found in the folder
        // the next uid is 1, we don't need to subtract
        // one so in order to provide the last uid
        if ($next === 1) {
            return $next;
        }

        return $next ? $next - 1 : 1;
    }

    /**
     * Get the folder next message uid
     *
     * @return int
     */
    public function getNextUid()
    {
        $status = $this->getEntity()->getStatus();

        // E.q. Outlook does not return the uidnext
        // In this case, we will sort the messages and will get the last message
        // uid, the last messageuid indicates the next uid
        if (! isset($status->uidnext)) {
            $lastMessageUid = $this->getAllUids()->max();

            return $lastMessageUid ? ($lastMessageUid + 1) : 1;
        }

        return $status->uidnext;
    }

    /**
     * Get folder all messages UID's
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllUids()
    {
        /**
         * We will use the \Ddeboer\Imap\MessageIterator Arrayiterator method
         * getArrayCopy to get the Id's only
         *
         * As if we loop throught the messages the package will
         * lazy load the messages structure
         */

        return collect($this->getEntity()->getMessages()->getArrayCopy());
    }

    /**
     * Get the folder unique identiier
     *
     * @return \App\Innoclapps\MailClient\FolderIdentifier
     */
    public function identifier()
    {
        return new FolderIdentifier('name', $this->getName());
    }

    /**
     * Bulk Set Flag for Messages.
     *
     * @param string $flag \Seen, \Answered, \Flagged, \Deleted, and \Draft
     * @param array|\Ddeboer\Imap\MessageIterator|string $numbers Message numbers
     */
    public function setFlag(string $flag, $numbers)
    {
        return $this->getEntity()->setFlag($flag, $numbers);
    }

    /**
     * Bulk Set Flag for Messages.
     *
     * @param string $flag \Seen, \Answered, \Flagged, \Deleted, and \Draft
     * @param array|\Ddeboer\Imap\MessageIterator|string $numbers Message numbers
     */
    public function clearFlag(string $flag, $numbers)
    {
        return $this->getEntity()->clearFlag($flag, $numbers);
    }

    /**
     * Mask a given message
     *
     * @param mixed $message
     * @param string $maskIntoClass
     *
     * @return \App\Innoclapps\MailClient\Imap\Message
     */
    protected function maskMessage($message, $maskIntoClass)
    {
        return (new $maskIntoClass($message))->setFolder($this);
    }

    /**
     * Add since search expression
     *
     * @param \Ddeboer\Imap\SearchExpression $expression
     * @param string $dateTime
     */
    protected function addSinceSearchExpression($expression, $dateTime)
    {
        $expression->addCondition($this->createSinceFilter($dateTime));
    }

    /**
     * Create since search filter
     *
     * @param string|\DateTimeImmutable $dateTime
     *
     * @return \Ddeboer\Imap\Search\Date\Since
     */
    protected function createSinceFilter($dateTime)
    {
        return new SinceFilter(
            $dateTime instanceof DateTimeImmutable ? $dateTime : new DateTime($dateTime)
        );
    }
}
