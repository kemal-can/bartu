<?php

namespace Tests\Fixtures;

use App\Innoclapps\Resources\Resource;

class CalendarResource extends Resource
{
    public static function repository()
    {
        return resolve(CalendarRepository::class);
    }

    public static function label() : string
    {
        return 'Calendars';
    }

    public static function singularLabel() : string
    {
        return 'Calendar';
    }

    public static function name() : string
    {
        return 'calendars';
    }

    public static function singularName() : string
    {
        return 'calendar';
    }

    public function associateableName() : string
    {
        return 'calendars';
    }

    public function jsonResource() : string
    {
        return CalendarJsonResource::class;
    }

    public function registerPermissions() : void
    {
        $this->registerCommonPermissions();
    }
}
