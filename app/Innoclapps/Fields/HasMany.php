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
use App\Innoclapps\Table\HasManyColumn;

class HasMany extends Optionable implements Countable
{
    use Selectable;
    use CountsRelationship;

    /**
     * @var string|null
     */
    protected $jsonResource;

    /**
     * Multi select custom component
     *
     * @var string
     */
    public $component = 'select-multiple-field';

    /**
     * @var string
     */
    public $hasManyRelationship;

    /**
     * Initialize new HasMany instance class
     *
     * @param string $attribute field attribute
     * @param string|null $label field label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->hasManyRelationship = $attribute;
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

        return parent::resolveForDisplay($model);
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
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\HasManyColumn
     */
    public function indexColumn() : HasManyColumn
    {
        return tap(new HasManyColumn(
            $this->hasManyRelationship,
            $this->labelKey,
            $this->label
        ), function ($column) {
            if ($this->counts()) {
                $column->count()->centered()->sortable();
            }
        });
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return null
     */
    public function mailableTemplatePlaceholder($model)
    {
        //
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
        return $model->relationLoaded($this->hasManyRelationship) && $this->jsonResource;
    }
}
