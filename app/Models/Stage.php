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

use App\Innoclapps\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'win_probability', 'display_order', 'pipeline_id',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'win_probability' => 'int',
        'display_order'   => 'int',
        'pipeline_id'     => 'int',
    ];

    /**
     * A stage belongs to pipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline()
    {
        return $this->belongsTo(\App\Models\Pipeline::class);
    }

    /**
     * A stage has many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function deals()
    {
        return $this->hasMany(\App\Models\Deal::class);
    }
}
