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

use App\Models\PredefinedMailTemplate;
use App\Innoclapps\Rules\UniqueRule;
use Illuminate\Foundation\Http\FormRequest;

class PredefinedMailTemplateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                UniqueRule::make(PredefinedMailTemplate::class, 'template'),
                'max:191',
            ],
            'subject'   => 'required|string|max:191',
            'body'      => 'required|string',
            'is_shared' => 'required|boolean',
        ];
    }
}
