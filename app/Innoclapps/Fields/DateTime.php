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
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Table\DateTimeColumn;
use App\Innoclapps\Contracts\Fields\Dateable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Fields\Customfieldable;
use App\Innoclapps\Fields\Dateable as DateableTrait;
use App\Innoclapps\MailableTemplates\Placeholders\DateTimePlaceholder;

class DateTime extends Field implements Customfieldable, Dateable
{
    use DateableTrait;

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'date-time-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d H:i:s'));
    }

    /**
      * Handle the resource record "creating" event
      *
      * @param \App\Innoclapps\Models\Model $model
      *
      * @return void
      */
    public function recordCreating($model)
    {
        if (! Innoclapps::isImportInProgress() || ! $model->usesTimestamps()) {
            return;
        }

        $timestampAttrs = [$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()];
        $request        = app(ResourceRequest::class);

        if ($request->has($this->requestAttribute()) &&
            in_array($this->attribute, $timestampAttrs) &&
            $model->isGuarded($this->attribute)) {
            $model->{$this->attribute} = $request->input($this->attribute);
        }
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
        $table->dateTime($fieldId)->nullable();
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
        return Format::dateTime($model->{$this->attribute});
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model|null $model
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders\DateTimePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return DateTimePlaceholder::make()
            ->value(fn () => $this->resolve($model))
            ->forUser($model?->user)
            ->tag($this->attribute)
            ->description($this->label);
    }

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\DateTimeColumn
     */
    public function indexColumn() : DateTimeColumn
    {
        return new DateTimeColumn($this->attribute, $this->label);
    }
}
