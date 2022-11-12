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

class Textarea extends Field implements Customfieldable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'textarea-field';

    /**
     * Textarea rows attribute
     *
     * @param mixed $rows
     *
     * @return static
     */
    public function rows($rows) : static
    {
        $this->withMeta(['attributes' => ['rows' => $rows]]);

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
        $table->text($fieldId)->nullable();
    }
}
