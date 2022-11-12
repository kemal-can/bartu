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

namespace App\Resources\User\Filters;

use App\Innoclapps\Filters\Select;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Repositories\UserRepository;

class User extends Select
{
    /**
     * Initialize User class
     *
     * @param string|null $label
     * @param string|null $field
     */
    public function __construct($label = null, $field = null)
    {
        parent::__construct($field ?? 'user_id', $label ?? __('user.user'));

        $this->withNullOperators()
            ->valueKey('id')
            ->labelKey('name');
    }

    /**
     * Provides the User instance options
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveOptions()
    {
        return resolve(UserRepository::class)->columns(['id', 'name'])
            ->orderBy('name')
            ->all()
            ->map(function ($user) {
                $isLoggedInUser = $user->is(Auth::user());

                return [
                    'id'   => ! $isLoggedInUser ? $user->id : 'me',
                    'name' => ! $isLoggedInUser ? $user->name : __('filters.me'),
                ];
            });
    }
}
