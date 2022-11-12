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

namespace App\Support\Concerns;

use App\Criteria\EmailAccount\EmailAccountsForUserCriteria;

trait HasEmails
{
    /**
     * Get all of the emails for the contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function emails()
    {
        return $this->morphToMany(
            \App\Models\EmailAccountMessage::class,
            'messageable',
            'email_account_messageables',
            null,
            'message_id'
        );
    }

    /**
     * A model has unread emails
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function unreadEmails()
    {
        return $this->emails()->where('is_read', false)->whereHas('folders', function ($folderQuery) {
            return $folderQuery->where('syncable', true);
        });
    }

    /**
     * Get the unread emails that the user can see
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function unreadEmailsForUser()
    {
        return $this->unreadEmails()->where(function ($query) {
            $query->whereHas('account', function ($accountQuery) {
                EmailAccountsForUserCriteria::applyQuery($accountQuery);
            })->whereHas('folders.account', function ($query) {
                return $query->whereColumn('folder_id', '!=', 'trash_folder_id');
            });
        });
    }
}
