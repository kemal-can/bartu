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

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Innoclapps\Rules\UniqueRule;
use App\Innoclapps\Translation\Translation;
use Illuminate\Foundation\Http\FormRequest;
use App\Innoclapps\Rules\ValidTimezoneCheckRule;

class ProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'  => ['required', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                UniqueRule::make(User::class, $this->user()->id),
            ],
            'time_format'       => ['required', 'string', Rule::in(config('app.time_formats'))],
            'date_format'       => ['required', 'string', Rule::in(config('app.date_formats'))],
            'first_day_of_week' => ['required', 'in:1,6,0', 'numeric'],
            'locale'            => ['required', 'string', Rule::in(Translation::availableLocales())],
            'timezone'          => ['required', 'string', new ValidTimezoneCheckRule],
        ];
    }
}
