<?php

namespace Tests\Fixtures;

use App\Innoclapps\Models\Model;

class Location extends Model
{
    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['locationable'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'display_name', 'location_type',
    ];

    /**
     * Get the locationables
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function locationable()
    {
        return $this->morphTo();
    }
}
