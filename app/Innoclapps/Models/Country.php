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

use Webpatser\Countries\Countries;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Country extends Countries
{
    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function name() : Attribute
    {
        return Attribute::get(function ($value) {
            $customKey = 'custom.country.' . $value;
            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }
}
