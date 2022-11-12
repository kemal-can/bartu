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

namespace App\Innoclapps\Contracts;

interface Countable
{
    /**
     * Set that the class should count
     *
     * @return self
     */
    public function count() : static;

    /**
     * Check whether the class counts
     *
     * @return boolean
     */
    public function counts() : bool;

    /**
     * Get the count key
     *
     * @return string
     */
    public function countKey() : string;
}
