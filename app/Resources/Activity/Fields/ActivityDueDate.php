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

namespace App\Resources\Activity\Fields;

use App\Models\Activity;
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Fields\Date;
use App\Innoclapps\Facades\Format;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\Facades\Request;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\MailableTemplates\Placeholders\DatePlaceholder;
use App\Innoclapps\MailableTemplates\Placeholders\DateTimePlaceholder;

class ActivityDueDate extends Date
{
    /**
     * The model attribute that hold the time
     *
     * @var string
     */
    protected $timeField = 'due_time';

    /**
     * The model attribute that holds the date
     *
     * @var string
     */
    protected $dateField = 'due_date';

    /**
     * Initialize new ActivityDueDate instance class
     */
    public function __construct($label)
    {
        parent::__construct($this->dateField, $label);

        $this->tapIndexColumn(function ($column) {
            return $column->width('180px')
                ->queryAs(Activity::dateTimeExpression($this->dateField, $this->timeField, $this->dateField))
                ->displayAs(function ($model) {
                    return $model->{$this->timeField} ? Format::dateTime(
                        $model->{$this->dateField}
                    ) : Format::date($model->{$this->dateField});
                });
        })->provideSampleValueUsing(fn () => date('Y-m-d') . ' 08:00:00')
            ->prepareForValidation(
                fn ($value, $request, $validator) => $this->mergeAttributesBeforeValidation($value, $request)
            );
    }

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'activity-due-date-field';

    /**
     * Resolve the field value for JSON Resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function resolveForJsonResource($model)
    {
        return [
            $this->attribute => [
                'date' => Carbon::parse($this->resolve($model))->format('Y-m-d'),
                'time' => $this->getTimeValue($model),
            ],
        ];
    }

    /**
     * Resolve the field value for export
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return string|null
     */
    public function resolveForExport($model)
    {
        $time = $this->getTimeValue($model);

        $carbonInstance = $this->dateTimeToCarbon($model->{$this->dateField}, $time);

        return $carbonInstance->format('Y-m-d' . ($time ? ' H:i:s' : ''));
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
        return Format::separateDateAndTime(
            $model->{$this->dateField},
            $model->{$this->timeField},
            $model->user
        );
    }

    /**
     * Resolve the field value for import
     *
     * @param string|null $value
     * @param array $row
     * @param array $original
     *
     * @return array
     */
    public function resolveForImport($value, $row, $original)
    {
        $value = parent::resolveForImport(
            $value,
            $row,
            $original
        )[$this->attribute];

        return $this->createSeparateDateAndTimeAttributes($value);
    }

    /**
     * Create the field storage data for the given request
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param string $requestAttribute
     *
     * @return array
     */
    public function getDataForStorage(ResourceRequest $request, $requestAttribute)
    {
        return $this->createSeparateDateAndTimeAttributes(
            $this->attributeFromRequest($request, $requestAttribute)
        );
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model|null $model
     *
     * @return string|null
     */
    public function mailableTemplatePlaceholder($model)
    {
        $placeholderClass = $model?->{$this->timeField} ?
            DateTimePlaceholder::class :
            DatePlaceholder::class;

        return $placeholderClass::make()
            ->formatUsing(function () use ($model) {
                return $this->resolveForDisplay($model);
            })
            ->tag($this->attribute)
            ->description($this->label);
    }

    /**
     * Create separate and and time attributes from the given value
     *
     * @param string|null $value
     *
     * @param string|null $dateAttribute
     * @param string|null $timeAttribute
     *
     * @return array
     */
    protected function createSeparateDateAndTimeAttributes($value, $dateAttribute = null, $timeAttribute = null)
    {
        $dateAttribute = ($dateAttribute ?: $this->dateField);
        $timeAttribute = ($timeAttribute ?: $this->timeField);

        [$date, $time] = [$value, null];

        if (Carbon::isISO8601($value)) {
            $value = Carbon::parse($value)->inAppTimezone();
        }

        if (! is_null($value) && str_contains($value, ' ')) {
            [$date, $time] = explode(' ', $value);
        }
        // Overrides if the date is provided in full e.q. 2021-12-14 12:00:00
        // and the user provide time field e.q. 14:00:00 the 14:00:00 will be used
        if (! $time && $this->resolveRequest()->has($timeAttribute)) {
            $time = $this->resolveRequest()->{$timeAttribute};
        }

        return [
            $dateAttribute => $date,
            $timeAttribute => $time,
        ];
    }

    /**
     * Merge the attributes before validating
     *
     * @param mixed $value
     * @param \App\Innoclapps\Resource\Http\ResourceRequest $request
     *
     * @return string|null
     */
    protected function mergeAttributesBeforeValidation($value, $request)
    {
        $attributes = $this->createSeparateDateAndTimeAttributes($value);

        // When provided the field as full date and time e.q. 2021-12-14 12:00:00 the time field
        // will be missing in the request, we need to merge it with the other fields
        if ($request->missing($this->timeField)) {
            $request->merge([$this->timeField => $attributes[$this->timeField]]);
        }

        // Return the actul date value from the parsed attributes
        return $attributes[$this->dateField];
    }

    /**
     * Get the time value from the model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string|null
     */
    protected function getTimeValue($model)
    {
        if (! $model->{$this->timeField}) {
            return null;
        }

        return $this->dateTimeToCarbon(
            $this->resolve($model),
            $model->{$this->timeField}
        )->format('H:i');
    }

    /**
     * Create Carbon UTC instance from the given date and time
     *
     * @param string $date
     * @param string|null $time
     *
     * @return \Carbon\Carbon
     */
    protected function dateTimeToCarbon($date, $time)
    {
        return Carbon::parse(
            Carbon::parse($date)->format('Y-m-d') . ($time ? ' ' . $time : '')
        );
    }

    /**
     * Resolve the current request
     *
     * @return \Illuminate\Http\Request
     */
    protected function resolveRequest()
    {
        if (Innoclapps::isImportInProgress()) {
            return Import::$currentRequest;
        }

        return Request::instance();
    }
}
