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

use App\Innoclapps\MailClient\FolderCollection;

trait MasksFolders
{
    /**
     * Ignored folders by well known name property fromm Microsoft
     *
     * @var array
     */
    protected $ignoredByWellKnownName = [
        'clutter',
        'conflicts',
        'conversationhistory',
        'outbox', // https://www.techwalla.com/articles/what-is-the-outbox-in-microsoft-outlook
        'recoverableitemsdeletions', // after deleted from the DELETE folder
        'scheduled',
        'syncissues',
    ];

    /**
     * Mask folders
     *
     * @param array $folders
     *
     * @return \App\Innoclapps\MailClient\FolderCollection
     */
    protected function maskFolders($folders)
    {
        return (new FolderCollection($folders))->map(function ($folder) {
            return $this->maskFolder($folder);
        })->reject(function ($folder) {
            // Email account draft folders are not supported
            return in_array($folder->getWellKnownName(), $this->ignoredByWellKnownName) || $folder->isDraft();
        })->values();
    }

    /**
     * Mask folder
     *
     * @param mixed $folder
     *
     * @return \App\Innoclapps\MailClient\Outlook\Folder
     */
    protected function maskFolder($folder)
    {
        return new Folder($folder);
    }
}
