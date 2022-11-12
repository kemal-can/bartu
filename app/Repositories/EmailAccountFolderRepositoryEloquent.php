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

namespace App\Repositories;

use App\Models\EmailAccount;
use App\Models\EmailAccountFolder;
use App\Models\EmailAccountMessage;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\MailClient\ConnectionType;
use App\Innoclapps\MailClient\FolderCollection;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use App\Contracts\Repositories\EmailAccountFolderRepository;

class EmailAccountFolderRepositoryEloquent extends AppRepository implements EmailAccountFolderRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return EmailAccountFolder::class;
    }

    /**
     * Get folders for account
     *
     * @param int $account Account id
     *
     * @return \App\Models\EmailAccount
     */
    public function getForAccount(int $account)
    {
        return $this->findWhere(['email_account_id' => $account]);
    }

    /**
     * Update folder for a given account
     *
     * @param \App\Models\EmailAccount $account
     * @param array $folder
     *
     * @return \App\Models\EmailAccountFolder
     */
    public function persistForAccount(EmailAccount $account, array $folder)
    {
        $parent = $this->updateOrCreate(
            $this->getUpdateOrCreateAttributes($account, $folder),
            array_merge($folder, [
                'email_account_id' => $account->id,
                'syncable'         => $folder['syncable'] ?? false,
            ])
        );

        $this->handleChildFolders($parent, $folder, $account);

        return $parent;
    }

    /**
     * Handle the child folders creation process
     *
     * @param \App\Models\EmailAccountFolder $parentFolder
     * @param array $folder
     * @param \App\Models\EmailAccount $account
     *
     * @return void
     */
    protected function handleChildFolders($parentFolder, $folder, $account)
    {
        // Avoid errors if the children key is not set
        if (! isset($folder['children'])) {
            return;
        }

        if ($folder['children'] instanceof FolderCollection) {
            /**
             * @see \App\Listeners\CreateEmailAccountViaOAuth
             */
            $folder['children'] = $folder['children']->toArray();
        }

        foreach ($folder['children'] as $child) {
            $parent = $this->persistForAccount($account, array_merge($child, [
                'parent_id' => $parentFolder->id,
            ]));

            $this->handleChildFolders($parent, $child, $account);
        }
    }

    /**
     * Mark the folder as not selectable and syncable
     *
     * @param int $id
     *
     * @return void
     */
    public function markAsNotSelectable(int $id)
    {
        $this->update(['syncable' => false, 'selectable' => false], $id);
    }

    /**
     * Count the total unread messages for a given folder
     *
     * @param int $folderId
     *
     * @return int
     */
    public function countUnreadMessages(int $folderId) : int
    {
        return $this->countReadOrUnreadMessages($folderId, 0);
    }

    /**
     * Count the total read messages for a given folder
     *
     * @param int $folderId
     *
     * @return int
     */
    public function countReadMessages(int $folderId) : int
    {
        return $this->countReadOrUnreadMessages($folderId, 1);
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model, $repository) {
            $repository->purge($model);
        });
    }

    /**
     * Purge the folder data
     *
     * @param \App\Models\EmailAccountFolder $folder
     *
     * @return void
     */
    protected function purge($folder)
    {
        // To prevent looping through all messages and loading them into
        // memory, we will get their id's only and purge the media
        // for the messages where media is available
        $messages = $folder->messages()->has('folders', '=', 1)->cursor()
            ->map(fn ($message) => $message->id);

        resolve(MediaRepository::class)
            ->purgeByMediableIds(EmailAccountMessage::class, $messages);

        $folder->messages()->has('folders', '=', 1)->delete();
    }

    /**
     * Count read or unread messages for a given folder
     *
     * @param int $folderId
     * @param int $isRead
     *
     * @return int
     */
    protected function countReadOrUnreadMessages($folderId, $isRead)
    {
        return $this->resetScope()
            ->resetCriteria()
            ->columns(['id'])
            ->withCount(['messages' => function ($query) use ($isRead) {
                return $query->where('is_read', $isRead);
            }])->findWhere(['id' => $folderId])->first()->messages_count ?? 0;
    }

    /**
     * Get the attributes that should be used for update or create method
     *
     * @param \App\Models\EmailAccount $account
     * @param array $folder
     *
     * @return array
     */
    protected function getUpdateOrCreateAttributes($account, $folder)
    {
        $attributes = ['email_account_id' => $account->id];

        // If the folder database ID is passed
        // use the ID as unique identifier for the folder
        if (isset($folder['id'])) {
            $attributes['id'] = $folder['id'];
        } else {
            // For imap account, we use the name as unique identifier
            // as the remote_id may not always be unique
            if ($account->connection_type === ConnectionType::Imap) {
                $attributes['name'] = $folder['name'];
            } else {
                // For API based accounts e.q. Gmail and Outlook
                // we use the remote_id as unique identifier
                $attributes['remote_id'] = $folder['remote_id'];
            }
        }

        return $attributes;
    }
}
