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
use App\Innoclapps\Contracts\Primaryable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Source extends Model implements Primaryable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * A source has many contacts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(\App\Models\Contact::class);
    }

    /**
     * A source has many companies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class);
    }

    /**
     * Check whether the source is primary
     *
     * @return boolean
     */
    public function isPrimary() : bool
    {
        return ! is_null($this->flag);
    }
}
