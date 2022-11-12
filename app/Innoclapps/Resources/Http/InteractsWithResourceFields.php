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

namespace App\Innoclapps\Resources\Http;

use App\Innoclapps\Facades\Fields;

trait InteractsWithResourceFields
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->setAuthorizedAttributes();

        $this->runValidationCallbacks($this->getValidatorInstance());
    }

    /**
     * Run the fields validation callbacks
     *
     * @return static
     */
    public function runValidationCallbacks($validator)
    {
        $original = $this->all();

        return with([], function ($data) use ($validator, $original) {
            foreach ($this->fieldsForValidationCallback() as $field) {
                $data[$field->requestAttribute()] = call_user_func_array(
                    $field->validationCallback,
                    [$this->{$field->requestAttribute()}, $this, $validator, $original]
                );
            }

            return $this->merge($data);
        });
    }

    /**
     * Get the fields applicable for validation callback
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    protected function fieldsForValidationCallback()
    {
        return $this->authorizedFields()->reject(function ($field) {
            return is_null($field->validationCallback) || $this->missing($field->requestAttribute());
        });
    }

    /**
     * Set the authorized attributes for the request
     *
     * @return void
     */
    protected function setAuthorizedAttributes()
    {
        // We will get all available fields for the current
        // request and based on the fields authorizations we will set
        // the authorized attributes, useful for example, field is not authorized to be seen
        // but it's removed from the fields method and in this case, if we don't check this here
        // this attribute will be automatically allowed as it does not exists in the authorized fields section
        // for this reason, we check this from all the available fields
        $fields = $this->allFields();

        $this->replace(collect($this->all())->filter(function ($value, $attribute) use ($fields) {
            return with($fields->findByRequestAttribute($attribute), function ($field) {
                return $field ? ($field->authorizedToSee() && ! $field->isReadOnly()) : true;
            });
        })->all());
    }

    /**
     * Get the associteables attributes but without any custom fields
     *
     * @return array
     */
    public function associateables()
    {
        $fields       = $this->authorizedFields();
        $associations = $this->resource()->availableAssociations();

        return collect($this->all())->filter(function ($value, $attribute) use ($associations, $fields) {
            // First, we will check if the attribute name is the special attribute "associations"
            if ($attribute === 'associations') {
                return true;
            }

            // Next, we will check if the attribute exists as available associateable
            // resource for the current resource, if exists, we will check if the resource is associateable
            // This helps to provide the associations on resources without fields defined
            $resource = $associations->first(function ($resource) use ($attribute) {
                return $resource->associateableName() === $attribute;
            });

            // If resource is found from the attribute and this resource
            // is associateble, we will return true for the filter
            if ($resource && $resource->isAssociateable()) {
                return true;
            }

            // Next, we will check if the attribute exists as field in the
            // authorized fields collection for the request
            $field = $fields->findByRequestAttribute($attribute);

            // Finally, we will check if it's a field and is multioptionable field
            return $field && $field->isMultiOptionable() && ! $field->isCustomField();
        })->all();
    }
}
