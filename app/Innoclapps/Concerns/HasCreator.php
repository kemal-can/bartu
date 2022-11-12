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

use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;

trait HasCreator
{
    /**
     * Boot HasCreator trait
     *
     * @return void
     */
    protected static function bootHasCreator()
    {
        static::creating(function ($model) {
            $foreignKeyName = (new static)->creator()->getForeignKeyName();

            if (! $model->{$foreignKeyName} && Auth::check()) {
                $model->{$foreignKeyName} = Auth::id();
            }
        });
    }

    /**
     * A resource has creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(
            Innoclapps::getUserRepository()->model(),
            'created_by'
        );
    }
}
