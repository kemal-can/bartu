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
use App\Innoclapps\Filters\DateTime;
use App\Innoclapps\QueryBuilder\Parser;

class ResourceNextActivityDate extends DateTime
{
    /**
     * Create new instance of ResourceNextActivityDate class
     */
    public function __construct()
    {
        parent::__construct('next_activity_date', __('fields.next_activity_date'));

        $this->withNullOperators()
            ->withoutOperators('was')
            ->query(function ($builder, $value, $condition, $sqlOperator, $rule, Parser $parser) {
                return $builder->whereHas(
                    'nextActivity',
                    function ($query) use ($value, $parser, $rule, $condition, $sqlOperator) {
                        $rule->query->rule = Activity::dueDateQueryExpression();

                        return $parser->makeQueryWhenDate($query, $this, $rule, $sqlOperator['operator'], $value, $condition);
                    }
                );
            });
    }
}
