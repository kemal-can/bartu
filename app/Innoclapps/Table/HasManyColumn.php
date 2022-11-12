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

use Illuminate\Support\Str;
use App\Innoclapps\Contracts\Countable;

class HasManyColumn extends RelationshipColumn implements Countable
{
    /**
     * HasMany columns are not by default sortable
     *
     * @var boolean
     */
    public bool $sortable = false;

    /**
      * Indicates whether on the relation count query be performed
      *
      * @var boolean
      */
    public bool $count = false;

    /**
     * Set that the column should count the results instead of quering all the data
     *
     * @return static
     */
    public function count() : static
    {
        $this->count     = true;
        $this->attribute = $this->countKey();

        return $this;
    }

    /**
     * Check whether a column query counts the relation
     *
     * @return boolean
     */
    public function counts() : bool
    {
        return $this->count === true;
    }

    /**
     * Get the count key
     *
     * @return string
     */
    public function countKey() : string
    {
        return Str::snake($this->attribute . '_count');
    }
}
