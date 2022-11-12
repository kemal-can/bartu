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

use App\Innoclapps\Facades\Innoclapps;

class ZapierHook extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hook', 'action', 'resource_name', 'data', 'user_id', 'zap_id'];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'data'    => 'array',
        'user_id' => 'int',
        'zap_id'  => 'int',
    ];

    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * A hook belongs to user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Innoclapps::getUserRepository()->model());
    }
}
