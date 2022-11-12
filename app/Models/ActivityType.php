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

class ActivityType extends Model implements Primaryable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'swatch_color', 'icon',
    ];

    /**
     * Get the calendars that the type is added as create event type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calendars()
    {
        return $this->hasMany(\App\Models\Calendar::class);
    }

    /**
     * Set the activity type
     *
     * @param int $id
     *
     * @return void
     */
    public static function setDefault(int $id) : void
    {
        settings(['default_activity_type' => $id]);
    }

    /**
     * Get the activity default type
     *
     * @return null|int
     */
    public static function getDefaultType() : ?int
    {
        return settings('default_activity_type');
    }

    /**
     * A activity type has many activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(\App\Models\Activity::class);
    }

    /**
     * Check whether the activity type is primary
     *
     * @return boolean
     */
    public function isPrimary() : bool
    {
        return ! is_null($this->flag);
    }
}
