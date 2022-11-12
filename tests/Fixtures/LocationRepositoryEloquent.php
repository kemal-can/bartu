<?php

namespace Tests\Fixtures;

use App\Innoclapps\Repository\AppRepository;

class LocationRepositoryEloquent extends AppRepository implements LocationRepository
{
    protected static $fieldSearchable = [
        'display_name' => 'like',
    ];

    public static function model()
    {
        return EventLocation::class;
    }
}
