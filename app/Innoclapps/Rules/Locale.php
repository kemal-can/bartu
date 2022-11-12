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

use ResourceBundle;
use Illuminate\Contracts\Validation\Rule;

class Locale implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! extension_loaded('intl')) {
            return (bool) preg_match('/^[A-Za-z_]+$/', $value);
        }

        return in_array($value, ResourceBundle::getLocales(''));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid locale, locale name should be in format: "de" or "de_DE" or "pt_BR"';
    }
}
