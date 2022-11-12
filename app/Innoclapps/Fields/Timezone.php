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

use App\Innoclapps\Facades\Timezone as Facade;
use App\Innoclapps\Rules\ValidTimezoneCheckRule;
use App\Innoclapps\Contracts\Fields\Customfieldable;

class Timezone extends Field implements Customfieldable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'timezone-field';

    /**
     * Initialize Timezone field
     *
     * @param string $attribute
     * @param string|null $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label ?? __('app.timezone'));

        $this->rules(new ValidTimezoneCheckRule)
            ->provideSampleValueUsing(fn () => array_rand(array_flip(tz()->all())));
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
      * Provide the options intended for Zapier
      *
      * @return array
      */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
              'timezones' => Facade::toArray(),
          ]);
    }
}
