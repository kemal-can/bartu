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

use App\Innoclapps\Table\BooleanColumn;
use App\Innoclapps\Contracts\Fields\Customfieldable;

class Boolean extends Field implements Customfieldable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'boolean-field';

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
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 1);
    }

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
     * Resolve the field value for export
     * The export value should be in the original database value
     * not e.q. Yes or No
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return $this->resolve($model);
    }

    /**
     * Resolve the displayable field value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        return $value === $this->trueValue ? __('app.yes') : __('app.no');
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
        $table->boolean($fieldId)->default(false)->nullable();
    }

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\BooleanColumn
     */
    public function indexColumn() : BooleanColumn
    {
        return tap(new BooleanColumn($this->attribute, $this->label), function ($column) {
            $column->trueValue($this->trueValue);
            $column->falseValue($this->falseValue);
        });
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'trueValue'  => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
