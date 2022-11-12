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

namespace App\Innoclapps\Contracts\Fields;

interface TracksMorphManyModelAttributes
{
    /**
     * Get the attributes the changes should be tracked on
     *
     * @return array|string
     */
    public function trackAttributes() : array|string;
}
