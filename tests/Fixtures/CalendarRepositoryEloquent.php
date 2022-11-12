<?php

namespace Tests\Fixtures;

use App\Innoclapps\Repository\AppRepository;

class CalendarRepositoryEloquent extends AppRepository implements CalendarRepository
{
    protected static $fieldSearchable = [
        'name' => 'like',
    ];

    public static function model()
    {
        return Calendar::class;
    }
}
