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

namespace App\Criteria;

use Illuminate\Support\Str;
use App\Models\UserOrderedModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Expression;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class UserOrderedModelCriteria extends QueriesByUserCriteria
{
    /**
     * Create new UserOrderedModelCriteria instance.
     *
     * @param \App\Models\User|null $user
     */
    public function __construct(protected $user = null)
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
    public function apply($query, RepositoryInterface $repository)
    {
        $table = (new UserOrderedModel)->getTable();

        return $query->select($this->qualifyColumns($query))
            ->leftJoin($table, function ($join) use ($query, $table) {
                $orderableModel = $query->getModel();

                $join->on($table . '.orderable_id', '=', $orderableModel->getTable() . '.' . $orderableModel->getKeyName())
                    ->where($table . '.orderable_type', $orderableModel::class)
                    ->where($table . '.user_id', $this->user ?: Auth::id());
            })
            ->orderBy($table . '.display_order', 'asc');
    }

    /**
     * Qualify the columns to avoid ambigious columns when joining
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return array|string
     */
    protected function qualifyColumns($builder)
    {
        $columns = $builder->getQuery()->columns;

        if (is_null($columns)) {
            return $builder->getModel()->getTable() . '.*';
        }

        return collect($columns)->map(function ($column) use ($builder) {
            if (! Str::endsWith($column, '.*') && ! $column instanceof Expression) {
                return $builder->getModel()->qualifyColumn($column);
            }

            return $column;
        })->all();
    }
}
