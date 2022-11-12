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

namespace App\Innoclapps\Fields;

use Illuminate\Support\Str;

trait CountsRelationship
{
    /**
     * Indicates whether the relation will be counted
     *
     * @var boolean
     */
    public bool $count = false;

    /**
     * Indicates that the relation will be counted
     *
     * @return static
     */
    public function count() : static
    {
        $this->count     = true;
        $this->attribute = Str::snake($this->attribute) . '_count';

        return $this;
    }

    /**
     * Check whether the field counts the relation
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
        return $this->attribute;
    }
}
