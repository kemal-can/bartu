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
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
    * Determine whether the user can view any products.
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
     * Determine whether the user can view the product.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Product $product
     *
     * @return boolean
     */
    public function view(User $user, Product $product)
    {
        if ($user->can('view all products')) {
            return true;
        }

        return (int) $product->created_by === (int) $user->id;
    }

    /**
     * Determine if the given user can create products.
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
     * Determine whether the user can update the product.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Product $product
     *
     * @return boolean|null
     */
    public function update(User $user, Product $product)
    {
        if ($user->can('edit own products')) {
            return (int) $user->id === (int) $product->created_by;
        }

        if ($user->can('edit all products')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the product.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Product $product
     *
     * @return boolean|null
     */
    public function delete(User $user, Product $product)
    {
        if ($user->can('delete own products')) {
            return (int) $user->id === (int) $product->created_by;
        }

        if ($user->can('delete any product')) {
            return true;
        }
    }
}
