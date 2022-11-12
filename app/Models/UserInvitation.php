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
use App\Innoclapps\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserInvitation extends Model
{
    use HasUuid;

    /**
     * The appended attributes
     *
     * @var array
     */
    protected $appends = ['link'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'roles', 'teams', 'super_admin', 'access_api'];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'super_admin' => 'boolean',
        'access_api'  => 'boolean',
        'roles'       => 'array',
        'teams'       => 'array',
    ];

    /**
     * Get the invitation link
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function link() : Attribute
    {
        return Attribute::get(fn () => route('invitation.show', ['token' => $this->token]));
    }

    /**
     * Get the model uuid column name
     *
     * @return string
     */
    protected static function getUuidColumnName() : string
    {
        return 'token';
    }
}
