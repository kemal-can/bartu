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
use App\Innoclapps\Timeline\Timelineable;
use App\Support\Concerns\HasEditorFields;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasComments,
        Resourceable,
        HasEditorFields,
        HasFactory,
        Timelineable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'user_id' => 'int',
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
     * Get all of the contacts that are assigned this note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function contacts()
    {
        return $this->morphedByMany(\App\Models\Contact::class, 'noteable');
    }

    /**
     * Get all of the companies that are assigned this note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function companies()
    {
        return $this->morphedByMany(\App\Models\Company::class, 'noteable');
    }

    /**
     * Get all of the deals that are assigned this note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function deals()
    {
        return $this->morphedByMany(\App\Models\Deal::class, 'noteable');
    }

    /**
     * Get the note owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the fields that are used as editor
     *
     * @return string
     */
    public function getEditorFields() : string
    {
        return 'body';
    }
}
