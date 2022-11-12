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

use Illuminate\Database\Eloquent\Builder;
use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class SearchByFirstNameAndLastNameCriteria implements CriteriaInterface
{
    /**
     * Initialze new class
     *
     * @param string|null $relation
     */
    public function __construct(protected $relation = null)
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
        $search = request('q');

        if ($raw = ($model instanceof Builder ? $model->getModel() : $model)->nameQueryExpression()) {
            if (! $this->relation) {
                return $model->orWhere(function ($query) use ($search, $raw) {
                    $query->where($raw, 'LIKE', '%' . $search . '%');
                });
            }

            return $model->orWhere($this->relation, function ($query) use ($search, $raw) {
                $query->where($raw, 'LIKE', '%' . $search . '%');
            });
        }

        return $model;
    }
}
