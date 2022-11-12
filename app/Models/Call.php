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
use App\Support\Concerns\HasComments;
use App\Innoclapps\Casts\ISO8601DateTime;
use App\Innoclapps\Timeline\Timelineable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Call extends Model
{
    use HasComments,
        Resourceable,
        HasFactory,
        Timelineable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body', 'date', 'call_outcome_id',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'date'            => ISO8601DateTime::class,
        'user_id'         => 'int',
        'call_outcome_id' => 'int',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->user_id = $model->user_id ?: auth()->id();
        });
    }

    /**
     * A call belongs to outcome
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function outcome()
    {
        return $this->belongsTo(\App\Models\CallOutcome::class, 'call_outcome_id');
    }

    /**
     * Get all of the contacts that are assigned this call.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function contacts()
    {
        return $this->morphedByMany(\App\Models\Contact::class, 'callable');
    }

    /**
     * Get all of the companies that are assigned this call.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function companies()
    {
        return $this->morphedByMany(\App\Models\Company::class, 'callable');
    }

    /**
     * Get all of the deals that are assigned this call.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function deals()
    {
        return $this->morphedByMany(\App\Models\Deal::class, 'callable');
    }

    /**
     * Get the call owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
