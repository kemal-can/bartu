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

use App\Innoclapps\Contracts\Countable;
use App\Innoclapps\Table\MorphManyColumn;

abstract class MorphMany extends Field implements Countable
{
    use CountsRelationship;

    /**
     * @var string|null
     */
    protected $jsonResource;

    /**
     * The display key for the model
     *
     * @var string
     */
    public $displayKey = '';

    /**
     * @var string
     */
    public $morphManyRelationship;

    /**
     * Initialize new MorphMany instance class
     *
     * @param string $attribute field attribute
     * @param string|null $label field label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->morphManyRelationship = $attribute;
    }

    /**
     * Set the JSON resource class for the HasMany relation
     *
     * @param string $resourceClass
     */
    public function setJsonResource($resourceClass)
    {
        $this->jsonResource = $resourceClass;

        return $this;
    }

    /**
     * Provide the displayable key
     *
     * @param string $key
     *
     * @return static
     */
    public function displayKey($key)
    {
        $this->displayKey = $key;

        return $this;
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

        if (is_string($value) || is_int($value)) {
            $value = array_map(fn ($label) => [$this->displayKey => trim($label)], explode(',', $value));
        }

        return [$this->attribute => $value];
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
        if ($this->counts()) {
            return $model->{$this->countKey()};
        }

        $value = parent::resolveForDisplay($model);

        if ($value->isNotEmpty()) {
            return $value->pluck($this->displayKey)->implode(', ');
        }
    }

    /**
     * Resolve the field value for export
     *
     * When countable and value is zero, is shown as empty
     * In this case, we cast the value as string
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return (string) parent::resolveForExport($model);
    }

    /**
     * Resolve the value for JSON Resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if ($this->counts()) {
            // We will check if the counted relation is null, this means that
            // the relation is not loaded, we will just return null to prevent the
            // attribute to be added in the JsonResource
            return is_null($model->{$this->countKey()}) ? null : [$this->countKey() => (int) $model->{$this->countKey()}];
        }

        if ($this->shouldResolveForJson($model)) {
            return with($this->jsonResource, function ($resource) use ($model) {
                return [
                    $this->attribute => $resource::collection($this->resolve($model)),
                ];
            });
        }
    }

    /**
     * Check whether the fields values should be resolved for JSON resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    protected function shouldResolveForJson($model)
    {
        return $model->relationLoaded($this->morphManyRelationship) && $this->jsonResource;
    }

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\HasManyColumn
     */
    public function indexColumn() : MorphManyColumn
    {
        return tap(new MorphManyColumn(
            $this->morphManyRelationship,
            $this->displayKey,
            $this->label
        ), function ($column) {
            $this->counts() ?
                $column->count()->centered()->sortable() :
                $column->displayAs(fn ($model) => $this->resolveForDisplay($model));
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
            'morphManyRelationship' => $this->morphManyRelationship,
        ]);
    }
}
