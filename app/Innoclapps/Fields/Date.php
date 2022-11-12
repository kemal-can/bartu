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

use App\Innoclapps\Facades\Format;
use App\Innoclapps\Table\DateColumn;
use App\Innoclapps\Contracts\Fields\Dateable;
use App\Innoclapps\Contracts\Fields\Customfieldable;
use App\Innoclapps\Fields\Dateable as DateableTrait;
use App\Innoclapps\MailableTemplates\Placeholders\DatePlaceholder;

class Date extends Field implements Customfieldable, Dateable
{
    use DateableTrait;

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'date-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d'));
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
        $table->date($fieldId)->nullable();
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
        return Format::date($model->{$this->attribute});
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model|null $model
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders\DatePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return DatePlaceholder::make()
            ->value(fn () => $this->resolve($model))
            ->forUser($model?->user)
            ->tag($this->attribute)
            ->description($this->label);
    }

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\DateColumn
     */
    public function indexColumn() : DateColumn
    {
        return new DateColumn($this->attribute, $this->label);
    }
}
