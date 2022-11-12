<?php

namespace Tests\Fixtures;

use App\Innoclapps\Repository\AppRepository;

class EventRepositoryEloquent extends AppRepository implements EventRepository
{
    protected static $fieldSearchable = [
        'title' => 'like',
    ];

    public static function model()
    {
        return Event::class;
    }
}
