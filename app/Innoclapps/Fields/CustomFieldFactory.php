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

use App\Innoclapps\Filters\Filter;
use App\Innoclapps\Models\CustomField;
use App\Innoclapps\Table\BelongsToColumn;
use App\Innoclapps\Table\MorphToManyColumn;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class CustomFieldFactory
{
    /**
     * The optionable custom field option id
     *
     * @var string
     */
    protected static string $optionId = 'id';

    /**
     * Filters namespace
     *
     * @var string
     */
    protected static string $filterNamespace = 'App\Innoclapps\Filters';

    /**
     * Fields namespace
     *
     * @var string
     */
    protected static string $fieldNamespace = __NAMESPACE__;

    /**
     * @var \App\Innoclapps\Contracts\Repositories\CustomFieldRepository
     */
    protected CustomFieldRepository $repository;

    /**
     * Create new CustomFieldFactory instance.
     *
     * @param string $resourceName
     */
    public function __construct(protected string $resourceName)
    {
        $this->repository = resolve(CustomFieldRepository::class);
    }

    /**
     * Create fields from custom fields intended for filters
     *
     * @return array
     */
    public function createFieldsForFilters() : array
    {
        $fields = [];

        foreach ($this->fields() as $field) {
            if ($instance = $this->createFilterInstance($field->field_type, $field)) {
                $fields[] = $instance;
            }
        }

        return $fields;
    }

    /**
     * Create field class from the given custom field
     *
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return \App\Innoclapps\Fields\Field
     */
    public static function createInstance(CustomField $field) : Field
    {
        $instance = static::createFieldInstance(static::$fieldNamespace, $field);

        // Default is hidden on index
        $instance->tapIndexColumn(fn ($column) => $column->hidden(true));

        $rules = [];

        if ($instance->isMultiOptionable()) {
            $rules[] = 'array';

            $instance->tapIndexColumn(fn ($column) => $column->queryAs('name as label'))
                ->saveUsing(function ($request, $requestAttribute, $value, $field) {
                    if (! $request->missing($requestAttribute)) {
                        return function ($model) use ($request, $requestAttribute, $value, $field) {
                            return app(CustomFieldRepository::class)->syncOptionsForModel(
                                $model,
                                $field,
                                $value,
                                $model->wasRecentlyCreated ? 'create' : 'update'
                            );
                        };
                    }
                })->resolveUsing(fn ($model, $attribute) => $field->prepareRelatedOptions($model));
        }

        if ($instance::class === Text::class) {
            $rules[] = 'max:191';
        }

        if ($instance->isOptionable()) {
            $instance->importUsing(function ($value, $row, $original, $field) {
                // The labelAsValue was unable to find id for the provided label
                // In this case, we will try to create the actual option in database
                if (is_null($value) && is_string($original[$field->attribute])) {
                    return [$field->attribute => static::getOptionIdViaLabel($field, $original[$field->attribute])];
                }

                return [$field->attribute => $value];
            });

            $instance->displayUsing(function ($model) use ($field, $instance) {
                return $instance->isMultiOptionable() ?
                    $model->{$field->relationName}->pluck('name')->implode(', ') :
                    $field->options->find($model->{$field->field_id})->name ?? '';
            });

            $instance->acceptLabelAsValue()->swapIndexColumn(fn () => $instance->isMultiOptionable() ?
            static::createColumnWhenMultiOptionable($field) :
            static::createColumnWhenSingleOptionable($field));
        }

        $instance->rules($rules);

        return $instance->setCustomField($field);
    }

    /**
     * Create new field class instance
     *
     * @param string $namespace
     * @param \App\Innoclapps\Models\CustomField $field
     * @param string|null $type
     *
     * @return \App\Innoclapps\Fields\Field|\App\Innoclapps\Filters\Filter
     */
    protected static function createFieldInstance(
        string $namespace,
        CustomField $field,
        string $type = null,
    ) : Field|Filter {
        $class    = '\\' . $namespace . '\\' . ($type ?? $field->field_type);
        $instance = (new $class($field->field_id, $field->label));

        if ($instance->isOptionable()) {
            $instance->valueKey(static::$optionId)->options($field->prepareOptions());
        }

        return $instance;
    }

    /**
     * Create filter instance from the given custom field
     *
     * @param string $type
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return \App\Innoclapps\Filter\Filter|boolean
     */
    protected function createFilterInstance($type, CustomField $field) : Filter|bool
    {
        if ($type == 'Textarea') {
            return false;
        } elseif ($type === 'Email') {
            $type = 'Text';
        } elseif ($type === 'Boolean') {
            $type = 'Radio';
        }

        $instance = static::createFieldInstance(static::$filterNamespace, $field, $type);

        if ($field->isMultiOptionable()) {
            $instance->query($this->multiOptionFilterQuery($field));
        } elseif ($field->field_type === 'Boolean') {
            $instance->options([true => __('app.yes'), false => __('app.no')]);
        }

        return $instance;
    }

    /**
     * Create table column when fields is multi optionable field
     *
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return \App\Innoclapps\Table\MorphToManyColumn
     */
    protected static function createColumnWhenMultiOptionable(CustomField $field) : MorphToManyColumn
    {
        $callback = function ($model) use ($field) {
            return $model->{$field->relationName}
                ->map(fn ($option) => $option->label)->implode(', ');
        };

        return (new MorphToManyColumn($field->relationName, 'name', $field->label))
            ->displayAs($callback);
    }

    /**
     * Create table column when field is single optionable field
     *
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return \App\Innoclapps\Table\BelongsToColumn
     */
    protected static function createColumnWhenSingleOptionable(CustomField $field) : BelongsToColumn
    {
        return new BelongsToColumn($field->relationName, 'name', $field->label);
    }

    /**
     * Create multi option filter query
     *
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return callable
     */
    protected function multiOptionFilterQuery(CustomField $field) : callable
    {
        return function ($builder, $value, $condition, $sqlOperator) use ($field) {
            $method = strtolower($sqlOperator['operator']) === 'in' ? 'whereHas' : 'whereDoesntHave';

            return $builder->{$method}($field->relationName, function ($query) use ($value) {
                return $query->whereIn('id', $value);
            });
        };
    }

    /**
     * Get the resource custom fields
     *
     * @return \Illuminate\Support\Collection
     */
    protected function fields()
    {
        return $this->repository->forResource($this->resourceName);
    }

    /**
     * Handle the label option when custom field is optionable
     *
     * @param \App\Innoclapps\Fields\Optionable $field
     * @param string $label The original provided label
     *
     * @return array|int
     */
    protected static function getOptionIdViaLabel(Optionable $field, $label)
    {
        if (! $field->isMultiOptionable()) {
            return static::resolveImportLabelOption($field, $label);
        }

        return with([], function ($value) use ($field, $label) {
            $labels = explode(',', $label);

            array_walk($labels, 'trim');

            foreach ($labels as $label) {
                $value[] = static::resolveImportLabelOption($field, $label);
            }

            return $value;
        });
    }

    /**
     * Get custom field option by given option label
     *
     * @param \App\Innoclapps\Fields\Optionable $field
     * @param string $label
     *
     * @return int
     */
    protected static function resolveImportLabelOption(Optionable $field, $label)
    {
        // First check if the option actually exists in the options collection
        // Perhaps was created in the below create code block
        if ($option = $field->optionByLabel($label)) {
            return $option[$field->valueKey];
        }

        // If option not found, we will create this option for the custom field
        $customField = resolve(CustomFieldRepository::class)->createOptions([
            'name' => $label,
        ], $field->customField);

        // Get fresh options and update the value
        $options = $customField->options()->get();
        $value   = $options->firstWhere('name', $label)->getKey();

        // Update field options and clear the cached collection
        $field->options($customField->prepareOptions($options))->clearCachedOptionsCollection();

        return $value;
    }
}
