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

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Rules\UniqueRule;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Models\CustomField;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Http\FormRequest;
use App\Innoclapps\Contracts\Resources\AcceptsCustomFields;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class CustomFieldRequest extends FormRequest
{
    /**
     * The attributes that cannot be used as custom field id
     *
     * @var array
     */
    protected $prohibitedAttributes = [
        'associations',
        'resource_name',
        'resource_id',
        'via_resource',
        'via_resource_id',
        'timeline',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'resource_name' => ['required', 'string', Rule::in(
                Innoclapps::registeredResources()
                    ->whereInstanceOf(AcceptsCustomFields::class)
                    ->map(fn ($resource) => $resource->name())
            )],
            'label'      => 'required|string|max:191',
            'field_type' => [Rule::requiredIf($this->isMethod('POST')), Rule::in(Fields::customFieldsTypes())],
            'field_id'   => $this->getFieldIdRules(),
            'options'    => ['nullable', 'array', function ($attribute, $value, $fail) {
                $customField = $this->isMethod('PUT') ?
                    resolve(CustomFieldRepository::class)->find($this->route('custom_field')) :
                    null;

                $fieldType = $this->isMethod('POST') ?
                        $this->field_type :
                        $customField->field_type;

                if (! in_array($fieldType, Fields::getOptionableFieldsTypes()) && count($value) > 0) {
                    $fail(__('fields.validation.refuses_options'));
                }

                if (in_array($fieldType, Fields::getOptionableFieldsTypes()) && empty($value)) {
                    $fail(__('fields.validation.requires_options'));
                }
            }],
        ];
    }

    /**
     * Get the field_id attribute rules
     *
     * @return array
     */
    protected function getFieldIdRules()
    {
        // Not rules as the field_id can't be updated once the field is created
        if ($this->isMethod('PUT')) {
            return [];
        }

        return [
               'required',
                'min:3',
                'max:64', // https://dev.mysql.com/doc/refman/5.7/en/identifier-length.html
                'regex:/^[a-z_]+$/',
                'starts_with:' . config('fields.custom_fields.prefix'),
                UniqueRule::make(CustomField::class, 'custom_field')
                    ->where('resource_name', $this->resource_name),
                    function ($attribute, $value, $fail) {
                        if (in_array($value, $this->prohibitedAttributes)) {
                            return $fail(__('fields.validation.invalid_name', ['name' => $value]));
                        }

                        $resource = Innoclapps::resourceByName($this->resource_name);

                        // First we will check if database column exists
                        if (Schema::hasColumn(app($resource->model())->getTable(), $value)) {
                            return $fail(__('fields.validation.exist'));
                        }

                        // Finally, we will check if there is actually field with the same attribute/id
                        // defined in the resource fields, includes default fields and custom fields
                        if ($resource->getFields($this)->find($value)) {
                            return $fail(__('fields.validation.exist'));
                        }
                    },
            ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'field_type.in'   => __('fields.validation.field_type_invalid'),
            'field_id.regex'  => __('fields.validation.field_id_invalid'),
            'field_id.unique' => __('fields.validation.exist'),
        ];
    }
}
