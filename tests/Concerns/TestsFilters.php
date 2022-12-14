<?php

namespace Tests\Concerns;

use Tests\Fixtures\EventRepository;
use Illuminate\Support\Facades\Request;
use App\Innoclapps\Criteria\FilterRulesCriteria;

trait TestsFilters
{
    /**
     * Perform filers search
     *
     * @param Criteria $criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function perform($attribute, $operand, $value = null)
    {
        $filter = app(static::$filter, ['field' => $attribute]);

        $rule = $this->payload(
            $attribute,
            $value,
            $filter->type(),
            $operand
        );

        return app(EventRepository::class)->pushCriteria(
            new FilterRulesCriteria($rule, collect([$filter]), Request::instance())
        )->all();
    }

    /**
     * Get filter payload
     *
     * @param string $field
     * @param mixed $value
     * @param string $type
     * @param string $operator
     *
     * @return array
     */
    protected function payload($field, $value, $type, $operator)
    {
        $rule = [
            'type'  => 'rule',
            'query' => [
                'type'     => $type,
                'rule'     => $field,
                'operator' => $operator,
                'value'    => $value,
            ],
        ];

        return [
            'condition' => 'and',
            'children'  => [$rule],
        ];
    }
}
