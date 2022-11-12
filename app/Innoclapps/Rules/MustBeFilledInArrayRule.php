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

class MustBeFilledInArrayRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected string $key, protected string $message)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param array $value
     * @return boolean
     */
    public function passes($attribute, $value)
    {
        return count(array_filter($value, function ($var) {
            return ($var && isset($var[$this->key]));
        })) === count($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
