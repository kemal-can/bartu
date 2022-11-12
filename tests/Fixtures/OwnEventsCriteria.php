<?php

namespace Tests\Fixtures;

use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class OwnEventsCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param Builder|Model $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (Auth::user()->can('view all events')) {
            return $model;
        }

        return $model->where('user_id', Auth::id());
    }
}
