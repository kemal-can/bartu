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

namespace App\Innoclapps\Models;

use Illuminate\Support\Str;
use App\Innoclapps\Fields\Field;
use Illuminate\Support\Collection;
use App\Innoclapps\Fields\CustomFieldFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CustomField extends Model
{
    /**
     * @var \App\Innoclapps\Fields\Field
     */
    protected $instance;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_type', 'field_id', 'resource_name', 'label',
    ];

    /**
     * A custom field has many options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(CustomFieldOption::class);
    }

    /**
     * Get the optionable custom field model relation name
     *
     * https://laravel.com/docs/7.x/eloquent-relationships#defining-relationships
     * "Relationship names cannot collide with attribute names as that could lead to your model not being able to know which one to resolve."
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function relationName() : Attribute
    {
        return Attribute::get(fn () => 'customField' . Str::studly($this->field_id));
    }

    /**
     * Get the instance from the field class
     *
     * @return \App\Innoclapps\Fields\Field
     */
    public function instance()
    {
        if (! $this->instance) {
            $this->instance = CustomFieldFactory::createInstance($this);
        }

        return $this->instance;
    }

    /**
     * Check whether the custom field is multi optionable
     *
     * @return boolean
     */
    public function isMultiOptionable() : bool
    {
        return $this->instance()->isMultiOptionable();
    }

    /**
     * Check whether the custom field is not multi optionable
     *
     * @return boolean
     */
    public function isNotMultiOptionable() : bool
    {
        return ! $this->isMultiOptionable();
    }

    /**
     * Check whether the custom field is optionable
     *
     * @return boolean
     */
    public function isOptionable() : bool
    {
        return $this->instance()->isOptionable();
    }

    /**
     * Check whether the custom field is not optionable
     *
     * @return boolean
     */
    public function isNotOptionable() : bool
    {
        return ! $this->isOptionable();
    }

    /**
     * Prepate the selected options for front-end
     *
     * @param \Illuminate\Database\Eloquent\Model $related
     *
     * @return array
     */
    public function prepareRelatedOptions($related) : array
    {
        return $this->prepareOptions($related->{$this->relationName});
    }

    /**
     * Prepare the options for front-end
     *
     * @param \Illuminate\Support\Collection|null $options
     *
     * @return array
     */
    public function prepareOptions(?Collection $options = null) : array
    {
        return ($options ?? $this->options)->map(function ($option) {
            return [
                'id'    => $option->id,
                'label' => $option->name,
            ];
        })->all();
    }
}
