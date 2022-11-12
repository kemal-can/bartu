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
use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'to'  => 'bail|required|array',
            'cc'  => 'bail|nullable|array',
            'bcc' => 'bail|nullable|array',
            // If changing the validation for recipients
            // check the front-end too
            'to.*.address'    => 'email',
            'cc.*.address'    => 'email',
            'bcc.*.address'   => 'email',
            'subject'         => 'required|string|max:191',
            'via_resource'    => Rule::requiredIf($this->filled('task_date')),
            'via_resource_id' => Rule::requiredIf($this->filled('task_date')),
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
            'to.*.address' => 'email address',
        ];
    }
}
