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

namespace App\Criteria\EmailAccount;

use App\Criteria\QueriesByUserCriteria;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class EmailAccountsForUserCriteria extends QueriesByUserCriteria
{
    /**
     * Apply criteria in query repository
     *
     * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     * @param \App\Innoclapps\Contracts\Repository\RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where(function ($query) {
            return self::applyQuery($query, $this->user, $this->columnName);
        });
    }

    /**
     * Apply the query for the criteria
     *
     * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     * @param \App\Models\User|int|null $user
     * @param string $columnName
     *
     * @return \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    public static function applyQuery($model, $user = null, $columnName = 'user_id')
    {
        $user = static::determineUser($user);

        $model->whereHas('user', function ($subQuery) use ($user, $columnName) {
            return parent::applyQuery($subQuery, $user, $columnName);
        });

        if ($user->can('access shared inbox')) {
            $model->orDoesntHave('user');
        }

        return $model;
    }
}
