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

namespace App\Innoclapps\Filters;

use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\QueryBuilder\Parser;

class HasMany extends OperandFilter
{
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
        if ($parser->ruleCountsRelation($rule->operand->rule)) {
            return $parser->makeQueryWhenCountableRelation(
                $builder,
                $rule->operand->rule,
                $rule,
                $sqlOperator['operator'],
                $value,
                $condition,
                function ($builder) {
                    return $this->applyOwnCriteriaIfNeeded($builder);
                }
            );
        }

        return $builder->has($this->field(), '>=', 1, $condition, function ($builder) use ($rule, $parser) {
            $builder = $this->applyOwnCriteriaIfNeeded($builder);

            // Use AND for the subquery of the relation rules
            return $parser->makeQuery($builder, $rule, 'AND');
        });
    }

    /**
     * Apply own criteria to the builder if the builder model
     * is associated with resources e.q. in has or whereHas
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyOwnCriteriaIfNeeded($builder)
    {
        if ($resource = Innoclapps::resourceByModel($builder->getModel())) {
            if ($ownCriteria = $resource->ownCriteria()) {
                // We will pass the repository fictional only
                $builder = (new $ownCriteria)->apply($builder, $resource->repository());
            }
        }

        return $builder;
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
