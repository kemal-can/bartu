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

namespace App\Innoclapps\Calendar;

use App\Innoclapps\AbstractMask;

class AbstractCalendar extends AbstractMask
{
    /**
     * Serialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'    => $this->getId(),
            'title' => $this->getTitle(),
        ];
    }
}
