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

use App\Innoclapps\Contracts\Fields\Customfieldable;

class Text extends Field implements Customfieldable
{
    /**
     * This field support input group
     *
     * @var boolean
     */
    public bool $supportsInputGroup = true;

    /**
     * Input type
     *
     * @var string
     */
    public string $inputType = 'text';

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'text-field';

    /**
     * Specify type attribute for the text field
     *
     * @param string $type
     *
     * @return static
     */
    public function inputType($type)
    {
        $this->inputType = $type;

        return $this;
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
        $table->string($fieldId)->nullable();
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'inputType' => $this->inputType,
        ]);
    }
}
