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

namespace App\Innoclapps\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidTimezoneCheckRule implements Rule
{
    /**
     * The rule checks if a passed timezone is valid timezone
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return boolean
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true;
        }

        return in_array($value, tz()->all());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.timezone');
    }
}
