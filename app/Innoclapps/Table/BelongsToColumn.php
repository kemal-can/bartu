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

namespace App\Innoclapps\Table;

class BelongsToColumn extends RelationshipColumn
{
    /**
     * @var callable|null
     */
    public $orderColumnCallback;

    /**
     * Add custom order column name callback
     *
     * @param callable $callback
     *
     * @return static
     */
    public function orderByColumn(callable $callback) : static
    {
        $this->orderColumnCallback = $callback;

        return $this;
    }
}
