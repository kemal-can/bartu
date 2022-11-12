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

use App\Models\User;
use App\Enums\SyncState;
use App\Models\EmailAccount;
use App\Models\EmailAccountMessage;
use App\Innoclapps\Contracts\Metable;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use App\Criteria\EmailAccount\EmailAccountsForUserCriteria;
use App\Contracts\Repositories\EmailAccountFolderRepository;
use App\Contracts\Repositories\EmailAccountMessageRepository;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class EmailAccountRepositoryEloquent extends AppRepository implements EmailAccountRepository
{
    /**
    * @var \App\Contracts\Repositories\EmailAccountFolderRepository
    */
    protected $folderRepository;

    /**
    * Specify Model class name
    *
    * @return string
    */
    public static function model()
    {
        return EmailAccount::class;
    }

    /**
    * Create email account
    *
    * @param array $attributes
    *
    * @return \App\Models\EmailAccountFolder
    */
    public function create(array $attributes)
    {
        $account = parent::create($attributes);

        if (! isset($attributes['user_id'])) {
            $fromName = ($attributes['from_name_header'] ?? '') ?: EmailAccount::DEFAULT_FROM_NAME_HEADER;
            $account->setMeta('from_name_header', $fromName);
        }

        foreach ($attributes['folders'] ?? [] as $folder) {
            $this->getFolderRepository()->persistForAccount($account, $folder);
        }

        foreach (['trash', 'sent'] as $folderType) {
            if ($folder = $account->folders->firstWhere('type', $folderType)) {
                tap($account, function ($instance) use ($folder, $folderType) {
                    $instance->{$folderType . 'Folder'}()->associate($folder);
                })->save();
            }
        }

        return $account;
    }

    /**
    * Perform model insert operation
    *
    * @param \Illuminate\Database\Eloquent\Model $model
    * @param array $attributes
    *
    * @return void
    */
    protected function performInsert($model, $attributes)
    {
        // If user exists, mark the account as personal before insert
        if (isset($attributes['user_id'])) {
            $model->forceFill(['user_id' => $attributes['user_id']]);
        }

        parent::performInsert($model, $attributes);
    }

    /**
    * Update email account
    *
    * @param array $attributes
    * @param mixed $id
    *
    * @return \App\Models\EmailAccountFolder
    */
    public function update(array $attributes, $id)
    {
        $account = parent::update($attributes, $id);

        if ($account->isShared() && isset($attributes['from_name_header'])) {
            $account->setMeta('from_name_header', $attributes['from_name_header']);
        }

        foreach ($attributes['folders'] ?? [] as $folder) {
            $this->getFolderRepository()->persistForAccount($account, $folder);
        }

        return $account;
    }

    /**
    * Set the account synchronization state
    *
    * @param int $id
    * @param \App\Enums\SyncState $state
    * @param string|null $comment
    *
    * @return void
    */
    public function setSyncState($id, SyncState $state, $comment = null)
    {
        $this->unguarded(function ($repository) use ($id, $state, $comment) {
            $repository->update([
                'sync_state'         => $state,
                'sync_state_comment' => $comment,
            ], $id);
        });
    }

    /**
    * Enable account synchronization
    *
    * @param int $id
    */
    public function enableSync($id)
    {
        $this->setSyncState($id, SyncState::ENABLED);
    }

    /**
    * Get syncable email accounts
    *
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function getSyncable()
    {
        return $this->orderBy('email', 'asc')->findByField('sync_state', SyncState::ENABLED);
    }

    /**
    * Count the unread messages for all accounts the given user can access
    *
    * @param \App\Models\User $user
    *
    * @return int
    */
    public function countUnreadMessagesForUser($user) : int
    {
        $result = $this->columns('id')
            ->resetScope()
            ->resetCriteria()
            ->pushCriteria(new EmailAccountsForUserCriteria($user))
            ->groupBy('id')
            ->withCount(['messages' => function ($query) {
                return $query->where('is_read', 0)
                    ->whereHas('folders', fn ($folderQuery) => $folderQuery->where('syncable', true));
            }])
            ->all()
            ->reduce(fn ($carry, $item) => $carry + $item['messages_count'], 0);

        $this->popCriteria(EmailAccountsForUserCriteria::class);

        return $result;
    }

    /**
    * Count the total unread messages for a given account
    *
    * @param int $accountId
    *
    * @return int
    */
    public function countUnreadMessages(int $accountId) : int
    {
        return $this->countReadOrUnreadMessages($accountId, 0);
    }

    /**
    * Count the total read messages for a given account
    *
    * @param int $accountId
    *
    * @return int
    */
    public function countReadMessages(int $accountId) : int
    {
        return $this->countReadOrUnreadMessages($accountId, 1);
    }

    /**
    * Get all shared email accounts
    *
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function getShared()
    {
        return $this->doesntHave('user')
            ->orderBy('email', 'asc')
            ->all();
    }

    /**
    * Get all user personal email accounts
    *
    * @param int $userId
    *
    * @return \Illuminate\Database\Eloquent\Collection
    */
    public function getPersonal($userId)
    {
        return $this->orderBy('email', 'asc')->findWhere(['user_id' => $userId]);
    }

    /**
    * Set that this account requires authentication
    *
    * @param int $id
    * @param boolean $value
    *
    * @return void
    */
    public function setRequiresAuthentication($id, $value = true)
    {
        $account = $this->find($id);

        if (! is_null($account->oAuthAccount)) {
            resolve(OAuthAccountRepository::class)->update(
                ['requires_auth' => $value],
                $account->oAuthAccount->id
            );
        }

        $this->update(['requires_auth' => $value], $account->id);
    }

    /**
    * Mark the given account as primary for the given user
    *
    * @param \App\Models\EmailAccount $account
    * @param \App\Innoclapps\Contracts\Metable&\App\Models\User $user
    *
    * @return void
    */
    public function markAsPrimary(EmailAccount $account, Metable & User $user) : void
    {
        $account->markAsPrimary($user);
    }

    /**
    * Remove primary account
    *
    * @param \App\Innoclapps\Contracts\Metable&\App\Models\User$user
    *
    * @return void
    */
    public function removePrimary(Metable & User $user) : void
    {
        EmailAccount::unmarkAsPrimary($user);
    }

    /**
    * Find email account by email addresss
    *
    * @param string $email
    *
    * @return \App\Models\EmailAccount|null
    */
    public function findByEmail(string $email) : ?EmailAccount
    {
        return $this->findByField('email', $email)->first();
    }

    /**
    * Count read or unread messages for a given account
    *
    * @param int $accountId
    * @param int $isRead
    *
    * @return int
    */
    protected function countReadOrUnreadMessages(int $accountId, int $isRead) : int
    {
        return resolve(EmailAccountMessageRepository::class)->resetScope()
            ->resetCriteria()
            ->count(['email_account_id' => $accountId, 'is_read' => $isRead], 'id');
    }

    /**
    * Get the folder repository
    *
    * @return \App\Contracts\Repositories\EmailAccountFolderRepository
    */
    public function getFolderRepository()
    {
        if (! is_null($this->folderRepository)) {
            return $this->folderRepository;
        }

        return $this->folderRepository = resolve(EmailAccountFolderRepository::class);
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
    * Purge the account data
    *
    * @param \App\Models\EmailAccount $account
    *
    * @return void
    */
    protected function purge($account)
    {
        // Detach from only messages with associations
        // This helps to not loop over all messages and delete them
        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $account->messages()->whereHas($relation, function ($query) {
                $query->withTrashed();
            })->cursor()->each(function ($message) use ($relation) {
                $message->{$relation}()->withTrashed()->detach();
            });
        }
        // To prevent looping through all messages and loading them into
        // memory, we will get their id's only and purge the media for the messages where media is available
        $messagesIds = $account->messages()->cursor()->map(fn ($message) => $message->id);

        resolve(MediaRepository::class)
            ->purgeByMediableIds(EmailAccountMessage::class, $messagesIds);

        $account->messages()->delete();

        $this->getFolderRepository()->delete($account->folders);

        $systemEmailAccountId = settings('system_email_account_id');

        if ((int) $systemEmailAccountId === (int) $account->id) {
            settings()->forget('system_email_account_id')->save();
        }
    }

    /**
    * The relations that are required for the responsee
    *
    * @return array
    */
    protected function eagerLoad()
    {
        $this->withCount([
            'messages as unread_count' => fn ($query) => $query->where('is_read', false),
        ]);

        return [
            'user',
            'folders' => fn ($query) => $query->withCount([
                'messages as unread_count' => fn ($query) => $query->where('is_read', false),
            ]),
            'sentFolder',
            'trashFolder',
            'oAuthAccount',
        ];
    }
}
