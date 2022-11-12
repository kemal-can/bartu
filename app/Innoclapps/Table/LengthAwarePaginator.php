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

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;

class LengthAwarePaginator extends BaseLengthAwarePaginator
{
    /**
     * @var integer
     */
    protected int $allTimeTotal = 0;

    /**
     * Set the all time total
     *
     * @param integer $total
     *
     * @return static
     */
    public function setAllTimeTotal(int $total) : static
    {
        $this->allTimeTotal = $total;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'all_time_total' => $this->allTimeTotal,
        ]);
    }
}
