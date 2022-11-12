<?php

namespace Tests\Fixtures;

use App\Innoclapps\Repository\AppRepository;

class EventStatusRepositoryEloquent extends AppRepository implements EventStatusRepository
{
    protected static $fieldSearchable = [
        'name' => 'like',
    ];

    public static function model()
    {
        return EventStatus::class;
    }
}
