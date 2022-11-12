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

class Setting extends Model
{
    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'user_id' => 'int',
    ];
}
