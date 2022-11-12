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

namespace App\Support\ChangeLoggers;

use Closure;
use Spatie\Activitylog\EventLogBag;
use Spatie\Activitylog\Contracts\LoggablePipe;

class CustomFieldsChangesLogger implements LoggablePipe
{
    /**
    * @var array
    */
    protected static array $oldCustomFieldOptionValues = [];

    /**
    * Initialize new CustomFieldsChangesLogger instance
    *
    * @param string $modelName
    */
    public function __construct(string $modelName)
    {
        $this->trackMultiOptionableCustomFieldChanges($modelName);
    }

    /**
    * Handle the pipe
    *
    * @param \Spatie\Activitylog\EventLogBag $event
    * @param \Closure $next
    *
    * @return \Spatie\Activitylog\EventLogBag
    */
    public function handle(EventLogBag $event, Closure $next) : EventLogBag
    {
        if (method_exists($event->model, 'getCustomFields')) {
            $customFields = $event->model->getCustomFields();

            $event->changes = collect($event->changes)->map(
                fn ($props) => $this->formatRegularAndSingleOptionableFieldProperties($props, $customFields)
            )->all();
        }

        return $next($event);
    }

    /**
    * Register event listener to track the multie optionable custom fields changes
    *
    * @param string $modelName
    *
    * @return void
    */
    protected function trackMultiOptionableCustomFieldChanges(string $modelName)
    {
        $modelName::beforeSyncCustomFieldOptions($this->beforeSyncCustomFieldOptionsCallback($modelName));
        $modelName::afterSyncCustomFieldOptions($this->afterSyncCustomFieldOptionsCallback($modelName));
    }

    /**
     * Get the callback for before sync custom field options
     *
     * @param string $modelName
     *
     * @return \Closure
     */
    protected function beforeSyncCustomFieldOptionsCallback(string $modelName)
    {
        return function ($model, $field, $attributes, $action) use ($modelName) {
            if ($action == 'update') {
                static::$oldCustomFieldOptionValues[$modelName][$field->attribute] = $field->resolveForDisplay($model);
            }
        };
    }

    /**
     * Get the callback for after sync custom field options
     *
     * @param string $modelName
     *
     * @return \Closure
     */
    protected function afterSyncCustomFieldOptionsCallback(string $modelName)
    {
        return function ($model, $field, $attributes, $action) use ($modelName) {
            if ($action == 'update') {
                $newValue = $field->resolveForDisplay(
                    $model->load($field->customField->relationName)
                );

                $oldValue = static::$oldCustomFieldOptionValues[$modelName][$field->attribute];

                if ($newValue == $oldValue) {
                    return;
                }

                static::logMultiOptionableChangelog($newValue, $oldValue, $model, $field);
            }
        };
    }

    /**
    * Log multioptionable custom field options changed activity
    *
    * @param string $newValue
    * @param string $oldValue
    * @param \Illuminate\Database\Eloquent\Model $model
    * @param \App\Innoclapps\Contracts\Fields\Customfieldable&\App\Innoclapps\Fields\Field $field
    *
    * @return void
    */
    protected static function logMultiOptionableChangelog($newValue, $oldValue, $model, $field)
    {
        $model::logDirtyAttributesOnLatestLog([
            'attributes' => [
                $field->attribute => [
                    'label' => $field->label,
                    'value' => $newValue,
                ],
            ],
            'old' => [
                $field->attribute => [
                    'label' => $field->label,
                    'value' => $oldValue,
                ],
            ],
        ], $model);
    }

    /**
    * Ensures custom fields properties are properly logged
    * Used for single optionable and regular custom field
    *
    * @param array $properties
    * @param \Illuminate\Support\Collection $customFields
    *
    * @return array
    */
    protected function formatRegularAndSingleOptionableFieldProperties($properties, $customFields)
    {
        $ids = $customFields->pluck('field_id')->all();

        foreach (array_keys($properties) as $property) {
            if (! in_array($property, $ids)) {
                continue;
            }

            if (! $customField = $customFields->firstWhere('field_id', $property)) {
                continue;
            }

            // Custom fields are formatted with label and value keys
            // Because if a user delete a custom field, the label will be lost
            // and we updated activity won't be shown properly without the label
            // In this case, we keep the label in the activity itself
            $properties[$property] = [
                'label' => $customField->label,
                'value' => with($properties[$property], function ($value) use ($customField, $properties) {
                    if ($customField->isOptionable()) {
                        return $customField->options->find($value)?->name ?? $value;
                    }

                    return $value;
                }),
            ];
        }

        return $properties;
    }
}
