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
use App\Innoclapps\Models\Import;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the import.
     *
     * @param \App\Models\User $user
     * @param \App\Innoclapps\Models\Import $import
     *
     * @return boolean
     */
    public function delete(User $user, Import $import)
    {
        return (int) $import->user_id === (int) $user->id;
    }
}
