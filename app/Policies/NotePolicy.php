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

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any notes.
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
     * Determine whether the user can view the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Note $note
     *
     * @return boolean
     */
    public function view(User $user, Note $note)
    {
        return (int) $user->id === (int) $note->user_id;
    }

    /**
     * Determine if the given user can create notes.
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
     * Determine whether the user can update the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Note $note
     *
     * @return boolean
     */
    public function update(User $user, Note $note)
    {
        return (int) $user->id === (int) $note->user_id;
    }

    /**
     * Determine whether the user can delete the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Note $note
     *
     * @return boolean
     */
    public function delete(User $user, Note $note)
    {
        return (int) $user->id === (int) $note->user_id;
    }
}
