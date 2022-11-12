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

namespace App\MailClient;

use App\Models\EmailAccount;
use App\MailClient\Exceptions\SyncFolderTimeoutException;

class EmailAccountSynchronizationManager
{
    /**
     * Time limit to sync account in seconds
     */
    const MAX_ACCOUNT_SYNC_TIME = 240;

    /**
     * Force mode indicator
     */
    const FORCE_MODE = 'force';

    /**
     * Chill mode indicator
     */
    const CHILL_MODE = 'chill';

    /**
     * Number of seconds passed to store last emails batch
     *
     * @var integer
     */
    protected $batchSaveTime = -1;

    /**
     * Timestamp when last batch was saved
     *
     * @var integer
     */
    protected $batchSaveTimestamp = 0;

    /**
     * Mode for the sync process
     *
     * @var string chill|force
     */
    protected $mode = self::CHILL_MODE;

    /**
     * @var \App\Console\Commands\EmailAccountsSynchronization|null
     */
    protected $command;

    /**
     * Get the synchronizer class
     *
     * @param \App\Models\EmailAccount $account
     *
     * @return mixed
     */
    public static function getSynchronizer(EmailAccount $account)
    {
        $part = $account->connection_type->value;

        return self::{'get' . $part . 'Synchronizer'}($account);
    }

    /**
     * Get the IMAP account synchronizer
     *
     * @param \App\Models\EmailAccount $account
     *
     * @return ImapEmailAccountSynchronization
     */
    public static function getImapSynchronizer(EmailAccount $account)
    {
        return resolve(ImapEmailAccountSynchronization::class, ['account' => $account]);
    }

    /**
     * Get the Gmail account synchronizer
     *
     * @param \App\Models\EmailAccount $account
     *
     * @return ImapEmailAccountSynchronization
     */
    public static function getGmailSynchronizer(EmailAccount $account)
    {
        return resolve(GmailEmailAccountSynchronization::class, ['account' => $account]);
    }

    /**
     * Get the Outlook account synchronizer
     *
     * @param \App\Models\EmailAccount $account
     *
     * @return ImapEmailAccountSynchronization
     */
    public static function getOutlookSynchronizer(EmailAccount $account)
    {
        return resolve(OutlookEmailAccountSynchronization::class, ['account' => $account]);
    }

    /**
     * Set the command class
     *
     * @param \App\Console\Commands\EmailAccountsSynchronization $command
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Check whether the synchronization is in force mode
     *
     * @return boolean
     */
    public function isForceMode()
    {
        return $this->mode === self::FORCE_MODE;
    }

    /**
     * Check whether the synchronization is in chill mode
     *
     * @return boolean
     */
    public function isChillMode()
    {
        return $this->mode === self::CHILL_MODE;
    }

    /**
     * Check timeout for done work with current origin
     * Exclude force mode
     *
     * @return boolean
     */
    protected function isTimeout()
    {
        if ($this->isForceMode()) {
            return false;
        }

        return time() - $this->processStartTime > self::MAX_ACCOUNT_SYNC_TIME;
    }

    /**
     * Log info if process invoked via command
     *
     * @param string $message
     *
     * @return void
     */
    protected function info($message)
    {
        if (! $this->command) {
            return;
        }

        $this->command->info($message);
    }

    /**
     * Log error if process invoked via command
     *
     * @param string $message
     *
     * @return void
     */
    protected function error($message)
    {
        if (! $this->command) {
            return;
        }

        $this->command->error($message);
    }

    /**
     * Tracks time when last batch was saved.
     *
     * Calculates time between batch saves.
     *
     * @param bool $isFolderSyncComplete
     * @param null|\App\Models\EmailAccountFolder $folder
     */
    protected function cleanUp($isFolderSyncComplete = false, $folder = null)
    {
        /**
         * In case folder sync completed and batch save time exceeded limit - throws exception.
         */
        if ($isFolderSyncComplete
            && $folder != null
            && $this->isChillMode()
            && $this->batchSaveTime > 0
            && $this->batchSaveTime > static::DB_BATCH_TIME
        ) {
            throw new SyncFolderTimeoutException($folder->account->email, $folder->name);
        } elseif ($isFolderSyncComplete) {
            /**
             * In case folder sync completed without batch save time exceed - reset batchSaveTime.
             */
            $this->batchSaveTime = -1;
        } else {
            /**
             * After batch save - calculate time difference between batches
             */
            if ($this->batchSaveTimestamp !== 0) {
                $this->batchSaveTime = time() - $this->batchSaveTimestamp;

                $this->info(sprintf('Batch save time: "%d" seconds.', $this->batchSaveTime));
            }
        }

        $this->batchSaveTimestamp = time();
    }
}
