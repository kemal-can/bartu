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

interface CountableRelation
{
    /**
     * Indicates that the filter will count the values of the relation
     *
     * @param string|null $relationName
     *
     * @return \App\Innoclapps\Filters\Filter
     */
    public function countableRelation($relationName = null);

    /**
     * Get the countable relation name
     *
     * @return string|null
     */
    public function getCountableRelation();
}
