<?php

namespace Tests\Fixtures;

use App\Models\User;
use App\Models\Comment;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Media\HasMedia;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model implements Presentable
{
    use HasFactory, Resourceable, HasMedia;

    protected $casts = [
        'start' => 'datetime',
        'end'   => 'datetime',
    ];

    protected $fillable = ['title', 'description', 'start', 'end', 'date', 'total_guests', 'is_all_day', 'user_id', 'status'];

    public function displayName() : Attribute
    {
        return Attribute::get(fn () => $this->title);
    }

    public function path() : Attribute
    {
        return Attribute::get(fn () => '/events/' . $this->getKey());
    }

    public function status()
    {
        return $this->belongsTo(EventStatus::class, 'status_id');
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable')->orderBy('locations.created_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calendars()
    {
        return $this->morphedByMany(Calendar::class, 'eventable', 'eventables');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at');
    }
}
