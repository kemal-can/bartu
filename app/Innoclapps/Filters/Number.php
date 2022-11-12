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

use Illuminate\Support\Str;

class Number extends Filter implements CountableRelation
{
    /**
     * The relation that the count is performed on
     *
     * @var string|null
     */
    public $countableRelation;

    /**
     * Indicates that the filter will count the val ues
     *
     * @param string|null $relationName
     *
     * @return \App\Innoclapps\Filters\Filter
     */
    public function countableRelation($relationName = null)
    {
        $this->countableRelation = $relationName ?? lcfirst(Str::studly($this->field()));
        $operators               = $this->getOperators();

        // between and not_between are not supported at this time.
        unset($operators[array_search('between', $operators)], $operators[array_search('not_between', $operators)]);

        $this->operators($operators);

        return $this;
    }

    /**
     * Get the countable relation name
     *
     * @return string|null
     */
    public function getCountableRelation()
    {
        return $this->countableRelation;
    }

    /**
     * Defines a filter type
     *
     * @return string
     */
    public function type() : string
    {
        return 'number';
    }
}
