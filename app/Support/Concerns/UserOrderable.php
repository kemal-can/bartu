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

use App\Models\UserOrderedModel;
use Illuminate\Support\Facades\Auth;

trait UserOrderable
{
    /**
     * Get the current user model order
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function userOrder()
    {
        return $this->morphOne(UserOrderedModel::class, 'orderable')->where('user_id', Auth::id());
    }
}
