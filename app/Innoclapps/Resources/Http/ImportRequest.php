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

class ImportRequest extends ResourcefulRequest
{
    /**
     * @var \App\Innoclapps\Fields\FieldCollection
     */
    protected $fields;

    /**
     * Get fields for the import
     *
     * @return \App\Innoclapps\Fields\FieldCollection
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * Get the authorized fields for import
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function authorizedFields()
    {
        return $this->fields();
    }

    /**
     * Set the fields for the import request
     *
     * @param \App\Innoclapps\Fields\FieldCollection $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the original row data
     *
     * @return array
     */
    public function original()
    {
        return $this->originalImport;
    }

    /**
     * Set the original row data
     *
     * @param array $row
     */
    public function setOriginal($row)
    {
        $this->originalImport = $row;

        return $this;
    }

    /**
     * Get the import validator
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
    * Get the error messages for the current resource request
    *
    * @return array
    */
    public function messages()
    {
        return [
            //
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //
    }

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validateResolved()
    {
        //
    }
}
