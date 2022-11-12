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
use App\Models\PredefinedMailTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class PredefinedMailTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\PredefinedMailTemplate $template
     *
     * @return boolean
     */
    public function view(User $user, PredefinedMailTemplate $template)
    {
        return (int) $user->id === (int) $template->user_id;
    }

    /**
     * Determine whether the user can update the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\PredefinedMailTemplate $template
     *
     * @return boolean
     */
    public function update(User $user, PredefinedMailTemplate $template)
    {
        return (int) $user->id === (int) $template->user_id;
    }

    /**
     * Determine whether the user can delete the note.
     *
     * @param \App\Models\User $user
     * @param \App\Models\PredefinedMailTemplate $template
     *
     * @return boolean
     */
    public function delete(User $user, PredefinedMailTemplate $template)
    {
        return (int) $user->id === (int) $template->user_id;
    }
}
