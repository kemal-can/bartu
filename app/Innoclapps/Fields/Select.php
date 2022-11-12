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

class Select extends Optionable implements Customfieldable
{
    use Selectable;

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'select-field';

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
