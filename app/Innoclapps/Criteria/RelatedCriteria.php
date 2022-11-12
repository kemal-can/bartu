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

class RelatedCriteria implements CriteriaInterface
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
        return $model->where(function ($query) use ($repository, $model) {
            $resource = ($model instanceof Builder ? $model->getModel() : $model)::resource();

            foreach ($resource->availableAssociations() as $key => $resource) {
                if ($ownCriteria = $resource->ownCriteria()) {
                    $whereCallback = function ($query) use ($repository, $ownCriteria) {
                        return resolve($ownCriteria)->apply($query, $repository);
                    };

                    $query->{$key === 0 ? 'whereHas' : 'orWhereHas'}($resource->associateableName(), $whereCallback);
                }
            }

            if (method_exists($model, 'user')) {
                $query->orWhere($model->user()->getForeignKeyName(), auth()->id());
            }

            return $query;
        });
    }
}
