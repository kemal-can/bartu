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

class Radio extends Optionable implements Customfieldable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'radio-field';

    /**
     * Indicates that the radio field will be inline
     *
     * @return static
     */
    public function inline() : static
    {
        $this->withMeta(['inline' => true]);

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
        $table->unsignedBigInteger($fieldId)->nullable();
        $table->foreign($fieldId)
            ->references('id')
            ->on('custom_field_options');
    }
}
