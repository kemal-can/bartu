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

class NumericFieldCheckRule implements Rule
{
    /**
     * The rule checks if a passed numeric/amount is valid
     * e.q. 1200.00 or 1200.000 is valid
     *
     * Note that the validation accepts maximum 3 decimals
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

        return preg_match('/^[0-9]\d*(\.\d{0,3})?$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.numeric_field');
    }
}
