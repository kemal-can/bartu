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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostReason extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'name',
    ];

    /**
     * A lost reason has many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function deals()
    {
        return $this->hasMany(\App\Models\Deal::class);
    }
}
