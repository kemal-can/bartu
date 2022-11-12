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

namespace App\Support\WebForm;

use Illuminate\Support\Arr;
use App\Innoclapps\Fields\DateTime;
use App\Innoclapps\Fields\BelongsTo;
use App\Innoclapps\Fields\MorphMany;
use App\Http\Requests\WebFormRequest;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Contracts\Fields\Dateable;

class FormSubmissionLogger
{
    /**
     * Changelog identifier
     */
    const IDENTIFIER = 'web-form-submission';

    /**
     * Initialize new FormSubmissionLogger instance
     *
     * @param array $resources
     * @param \App\Http\Requests\WebFormRequest $request
     *
     * @return void
     */
    public function __construct(protected array $resources, protected WebFormRequest $request)
    {
    }

    /**
     * Log the submission changelog
     *
     * @return \App\Innoclapps\Models\Changelog
     */
    public function log()
    {
        foreach ($this->resources as $resourceName => $model) {
            $activity = ChangeLogger::useModelLog()
                ->on($model)
                ->forceLogging()
                ->byAnonymous()
                ->identifier(static::IDENTIFIER)
                ->withProperties(
                    $this->properties($model)
                )->log();
        }

        return $activity;
    }

    /**
     * Get the changelog properties for the given model
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return array
     */
    protected function properties($model)
    {
        return array_merge(
            $this->propertiesFromFieldSections($model),
            $this->propertiesFromFileSections()
        );
    }

    /**
     * Get the changelog properties from the field sections
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return array
     */
    protected function propertiesFromFieldSections($model)
    {
        return $this->request->webForm()->fields()
            ->map(function ($field) use ($model) {
                return with([
                    'value'        => $this->request->getOriginalInput()[$field->requestAttribute()],
                    'attribute'    => $field->attribute,
                    'label'        => $field->label,
                    'resourceName' => $field->meta()['resourceName'],
                ], function ($attributes) use ($field, $model) {
                    if (! blank($attributes['value'])) {
                        if ($field instanceof Dateable) {
                            // Dates must be formatted on front-end for proper display in user timezone
                            $attributes[$field instanceof DateTime ? 'dateTime' : 'date'] = true;
                        } else {
                            $attributes['value'] = $this->displayValueFromField($attributes['value'], $field);
                        }
                    }

                    $attributes['value'] = ! blank($attributes['value']) ?
                    $attributes['value'] :
                    null;

                    return $attributes;
                });
            })->all();
    }

    /**
     * Get the changelog properties from the file sections
     *
     * @return array
     */
    protected function propertiesFromFileSections()
    {
        return collect($this->request->webForm()->fileSections())
            ->map(function ($section) {
                $attributes = [
                    'value'        => [],
                    'label'        => $section['label'],
                    'resourceName' => $section['resourceName'],
                ];

                foreach (Arr::wrap($this->request->getOriginalInput()[$section['requestAttribute']] ?? []) as $file) {
                    $attributes['value'][] = $file->getClientOriginalName() . ' (' . format_bytes($file->getSize()) . ')';
                }

                $attributes['value'] = count($attributes['value']) > 0 ? implode(', ', $attributes['value']) : null;

                return $attributes;
            })->all();
    }

    /**
     * Get the display value from the field
     *
     * @param mixed $value
     * @param \App\Innoclapps\Fields\Field $field
     *
     * @return mixed
     */
    protected function displayValueFromField($value, $field)
    {
        if ($field instanceof BelongsTo) {
            $value = $field->getRepository()->find($value)->{$field->labelKey};
        } elseif ($field instanceof MorphMany) {
            $value = with(collect(
                $this->request->getOriginalInput()[$field->requestAttribute()]
            ), function ($values) use ($field) {
                return $values->pluck($field->displayKey)->implode(', ');
            });
        } elseif ($field->isOptionable()) {
            $value = $this->displayValueWhenOptionableField($field, $value);
        }

        return $value;
    }

    /**
     * Get the value when optionable field
     *
     * @param \App\Innoclapps\Fields\Field $field
     *
     * @return string
     */
    protected function displayValueWhenOptionableField($field, $value)
    {
        if ($field->isMultiOptionable()) {
            return $field->isCustomField() ? $field->customField->options
                ->whereIn('id', $value)
                ->pluck('name')
                ->implode(', ') : collect($field->resolveOptions())
                ->whereIn($field->labelKey, $value)
                ->pluck($field->labelKey)->implode(', ');
        }

        return $field->isCustomField() ?
            $field->customField->options->find($value)->name :
            collect($field->resolveOptions())
                ->firstWhere($field->labelKey, $value)
                ->{$field->labelKey};
    }
}
