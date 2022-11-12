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

namespace App\Innoclapps\Filters;

use Exception;
use JsonSerializable;
use App\Innoclapps\Fields\ChangesKeys;

class Operand implements JsonSerializable
{
    use ChangesKeys;

    /**
     * @var \App\Innoclapps\Filters\Filter
     */
    public $rule;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $label;

    /**
     * Initialize Operand class
     *
     * @param mixed $value
     * @param string $label
     */
    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * Set the operand filter
     *
     * @param \App\Innoclapps\Filters\Fitler|string $rule
     *
     * @return \App\Innoclapps\Filters\Operand
     */
    public function filter($rule)
    {
        if (is_string($rule)) {
            $rule = $rule::make($this->value);
        }

        if ($rule instanceof HasMany) {
            throw new Exception('Cannot use HasMany filter in operands');
        }

        $this->rule = $rule;

        return $this;
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'value'    => $this->value,
            'label'    => $this->label,
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'rule'     => $this->rule,
        ];
    }
}
