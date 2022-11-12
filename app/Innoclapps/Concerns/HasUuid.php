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

namespace App\Innoclapps\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the model uuid generator trait
     *
     * @return void
     */
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (! $model->{static::getUuidColumnName()}) {
                $model->{static::getUuidColumnName()} = static::generateUuid();
            }
        });
    }

    /**
     * Generate model uuid
     *
     * @return string
     */
    public static function generateUuid() : string
    {
        $uuid = null;
        do {
            if (! static::where(static::getUuidColumnName(), $possible = Str::uuid())->first()) {
                $uuid = $possible;
            }
        } while (! $uuid);

        return $uuid;
    }

    /**
     * Get the model uuid column name
     *
     * @return string
     */
    protected static function getUuidColumnName() : string
    {
        return 'uuid';
    }
}
