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

class BooleanColumn extends Column
{
    /**
     * Initialize new BooleanColumn instance.
     *
     * @param string|null $attribute
     * @param string|null $label
     */
    public function __construct(?string $attribute = null, ?string  $label = null)
    {
        parent::__construct($attribute, $label);

        $this->centered();
    }

    /**
     * Checkbox checked value
     *
     * @var mixed
     */
    public mixed $trueValue = true;

    /**
     * Checkbox unchecked value
     *
     * @var mixed
     */
    public mixed $falseValue = false;

    /**
     * Data heading component
     *
     * @var string
     */
    public string $component = 'table-data-boolean-column';

    /**
     * Checkbox checked value
     *
     * @param mixed $val
     *
     * @return static
     */
    public function trueValue(mixed $val) : static
    {
        $this->trueValue = $val;

        return $this;
    }

    /**
     * Checkbox unchecked value
     *
     * @param mixed $val
     *
     * @return static
     */
    public function falseValue(mixed $val) : static
    {
        $this->falseValue = $val;

        return $this;
    }

    /**
     * Additional column meta
     *
     * @return array
     */
    public function meta() : array
    {
        return array_merge([
            'falseValue' => $this->falseValue,
            'trueValue'  => $this->trueValue,
        ], $this->meta);
    }
}
