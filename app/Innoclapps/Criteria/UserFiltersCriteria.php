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

namespace App\Innoclapps\Criteria;

use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class UserFiltersCriteria implements CriteriaInterface
{
    /**
     * Initialize criteria
     *
     * @param string $identifier
     * @param int $userId User id the filters are intended for
     */
    public function __construct(protected string $identifier, protected $userId)
    {
    }

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
        return $model->where('identifier', $this->identifier)
            ->where(function ($query) {
                return $query->where('filters.user_id', $this->userId)
                    ->orWhere('is_shared', true)
                    ->orWhereNull('user_id');
            });
    }
}
