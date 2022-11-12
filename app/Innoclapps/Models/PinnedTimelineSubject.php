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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PinnedTimelineSubject extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
    * The "booted" method of the model.
    *
    * @return void
    */
    protected static function booted()
    {
        static::addGlobalScope('default_order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }

    /**
     * Get the subject of the pinned timeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject() : MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the timelineable
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function timelineable() : MorphTo
    {
        return $this->morphTo();
    }
}
