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

namespace App\Policies;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any contacts.
    *
    * @param \App\Models\User  $user
    *
    * @return boolean
    */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the contact.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Contact $contact
     *
     * @return boolean
     */
    public function view(User $user, Contact $contact)
    {
        if ($user->can('view all contacts')) {
            return true;
        }

        return (int) $contact->user_id === (int) $user->id;
    }

    /**
     * Determine if the given user can create contacts.
     *
     * @param \App\Models\User $user
     *
     * @return boolean
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the contact.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Contact $contact
     *
     * @return boolean|null
     */
    public function update(User $user, Contact $contact)
    {
        if ($user->can('edit own contacts')) {
            return (int) $user->id === (int) $contact->user_id;
        }

        if ($user->can('edit all contacts')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the contact.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Contact $contact
     *
     * @return boolean|null
     */
    public function delete(User $user, Contact $contact)
    {
        if ($user->can('delete own contacts')) {
            return (int) $user->id === (int) $contact->user_id;
        }

        if ($user->can('delete any contact')) {
            return true;
        }
    }
}
