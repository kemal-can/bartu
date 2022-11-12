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

namespace App\Innoclapps\Fields;

class Number extends Text
{
    /**
     * Input type
     *
     * @var string
     */
    public string $inputType = 'number';

    /**
     * Initialize Numeric field
     *
     * @param string $attribute
     * @param string|null $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->rules(['nullable', 'integer'])
            ->provideSampleValueUsing(fn () => rand(1990, date('Y')));
    }

    /**
     * Create the custom field value column in database
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $fieldId
     *
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->integer($fieldId)->index()->nullable();
    }
}
