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

namespace App\Resources\Activity\Filters;

use App\Models\Activity;
use App\Innoclapps\Filters\Select;
use App\Innoclapps\QueryBuilder\Parser;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;

class ResourceActivitiesFilter extends Select
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * Initialize ResourceActivitiesFilter class
     */
    public function __construct()
    {
        parent::__construct('activities', __('activity.activities'), ['equal']);

        $this->options([
                'today'                  => __('dates.due.today'),
                'next_day'               => __('dates.due.tomorrow'),
                'this_week'              => __('dates.due.this_week'),
                'next_week'              => __('dates.due.next_week'),
                'this_month'             => __('dates.due.this_month'),
                'next_month'             => __('dates.due.next_month'),
                'this_quarter'           => __('dates.due.this_quarter'),
                'overdue'                => __('activity.overdue'),
                'doesnt_have_activities' => __('activity.doesnt_have_activities'),
            ])->displayAs([
                __('activity.filters.display.has'),
                'overdue'                => __('activity.filters.display.overdue'),
                'doesnt_have_activities' => __('activity.filters.display.doesnt_have_activities'),
            ]);
    }

    /**
     * Apply the filter when custom query callback is provided
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed $value
    *  @param string    $condition
    *  @param array     $sqlOperator
     * @param stdClass $rule
     * @param \App\Innoclapps\QueryBuilder\Parser $parser
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($builder, $value, $condition, $sqlOperator, $rule, Parser $parser)
    {
        if ($value == 'doesnt_have_activities') {
            return $builder->doesntHave('activities', $condition);
        }

        return $builder->has('activities', '>=', 1, $condition, function ($query) use ($value) {
            if ($value === 'overdue') {
                return $query->overdue();
            }

            return $query->whereBetween(Activity::dueDateQueryExpression(), $this->getBetweenArguments($value));
        });
    }

    /**
     * Check whether the filter has custom callback
     *
     * @return boolean
     */
    public function hasCustomQuery() : bool
    {
        return true;
    }
}
