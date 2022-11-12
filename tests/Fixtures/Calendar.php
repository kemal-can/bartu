<?php

namespace Tests\Fixtures;

use App\Innoclapps\Models\Model;
use App\Innoclapps\Resources\Resourceable;
use App\Innoclapps\Workflow\HasWorkflowTriggers;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calendar extends Model
{
    use HasFactory, Resourceable, HasWorkflowTriggers;

    protected $fillable = ['name', 'user_id'];

    protected $table = 'event_calendars';

    public function events()
    {
        return $this->morphToMany(Event::class, 'eventable', 'eventables');
    }
}
