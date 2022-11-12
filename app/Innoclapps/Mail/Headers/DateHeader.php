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

namespace App\Innoclapps\Mail\Headers;

use Illuminate\Support\Carbon;

class DateHeader extends Header
{
    /**
     * Get the header value
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getValue()
    {
        $tz = config('app.timezone');

        return $this->value ? Carbon::parse($this->value)->tz($tz) : null;
    }
}
