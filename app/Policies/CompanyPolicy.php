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
use App\Models\Company;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any companies.
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
     * Determine whether the user can view the company.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Company $company
     *
     * @return boolean
     */
    public function view(User $user, Company $company)
    {
        if ($user->can('view all companies')) {
            return true;
        }

        return (int) $company->user_id === (int) $user->id;
    }

    /**
     * Determine if the given user can create companies.
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
     * Determine whether the user can update the company.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Company $company
     *
     * @return boolean|null
     */
    public function update(User $user, Company $company)
    {
        if ($user->can('edit own companies')) {
            return (int) $user->id === (int) $company->user_id;
        }

        if ($user->can('edit all companies')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the company.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Company $company
     *
     * @return boolean|null
     */
    public function delete(User $user, Company $company)
    {
        if ($user->can('delete own companies')) {
            return (int) $user->id === (int) $company->user_id;
        }

        if ($user->can('delete any company')) {
            return true;
        }
    }
}
