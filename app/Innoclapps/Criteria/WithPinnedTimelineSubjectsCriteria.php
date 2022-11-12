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

use App\Innoclapps\Models\PinnedTimelineSubject;
use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class WithPinnedTimelineSubjectsCriteria implements CriteriaInterface
{
    /**
     * The subject model the associated are queries are intended for
     *
     * @param \App\Innoclapps\Models\Model
     */
    public function __construct(protected $subject)
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
        return static::applyQuery($model, $this->subject, $repository->getModel());
    }

    /**
     * Apply the query for the criteria
     *
     * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builde $query
     * @param \Illumindata\Database\Eloquent\Model $subject
     * @param \Illumindata\Database\Eloquent\Model $timelineable
     *
     * @return \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    public static function applyQuery($query, $subject, $timelineable)
    {
        $pinModel = new PinnedTimelineSubject;

        $callback = function ($join) use ($pinModel, $timelineable, $subject) {
            $join->on($timelineable->getQualifiedKeyName(), '=', $pinModel->getTable() . '.timelineable_id')
                ->where($pinModel->getTable() . '.timelineable_type', $timelineable::class)
                ->where($pinModel->getTable() . '.subject_id', $subject->getKey())
                ->where($pinModel->getTable() . '.subject_type', $subject::class);
        };

        return $query->leftJoin($pinModel->getTable(), $callback);
    }
}
