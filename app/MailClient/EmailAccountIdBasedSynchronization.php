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

use App\Innoclapps\MailClient\FolderCollection;

/**
 * This a class that support ID based synchronization
 * Where the ID's are unique e.q. for the folders and the messages
 * Not like IMAP, the ID's are not unique for messages nor for folders
 */
abstract class EmailAccountIdBasedSynchronization extends EmailAccountSynchronization
{
    /**
     * Sync email folders
     *
     * We will re-fetch the folders each time and perform the update
     * via the repository function as its is extremely hard to handle all the changes
     * that can be performed on a folder e.q. remove, add new child from another folder,
     * the move to another parent etc...
     *
     * @return void
     */
    public function syncFolders()
    {
        $this->removeRemotelyRemovedFolders();

        $folders = $this->rememberFoldersDatabaseState();

        $this->account = $this->accounts->update(
            ['folders' => $folders],
            $this->account->id
        )->load(['folders.account', 'sentFolder.account', 'trashFolder.account']);
    }

    /**
     * We will loop through all folders and remember the database state before
     * updating so it can remember the syncable folders and perform update
     *
     * @param \App\Innoclapps\MailClient\FolderCollection|null|array $childFolders
     *
     * @return \App\Innoclapps\MailClient\FolderCollection
     */
    protected function rememberFoldersDatabaseState($childFolders = null)
    {
        $collection = new FolderCollection;

        $remoteFolders = is_null($childFolders) ? $this->getFolders() : $childFolders;

        foreach ($remoteFolders as $remoteFolder) {
            $dbFolder     = $this->findDatabaseFolder($remoteFolder);
            $remoteFolder = $remoteFolder->toArray();

            $remoteFolder['children'] = $this->rememberFoldersDatabaseState($remoteFolder['children']);

            if ($dbFolder) {
                $remoteFolder['syncable'] = $dbFolder->syncable;

                // Add the ID so the EmailAccountRepository can recognize the folder
                $remoteFolder['id'] = $dbFolder->id;

                // Updated folder
                if ($dbFolder->name != $remoteFolder['name']) {
                    $this->synced = true;
                    $this->info(sprintf(
                        'Updating remotely renamed folder, OLD: %s, NEW: %s',
                        $dbFolder->name,
                        $remoteFolder['name']
                    ));
                }
            } else {
                // New folder
                $this->synced = true;
            }

            $collection->push($remoteFolder);
        }

        return $collection;
    }
}
