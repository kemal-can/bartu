<?php

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use App\Innoclapps\Fields\Date;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Fields\Editor;
use App\Innoclapps\Fields\Number;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Fields\Boolean;
use App\Innoclapps\Fields\DateTime;
use App\Http\Resources\UserResource;
use App\Innoclapps\Fields\BelongsTo;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Innoclapps\Contracts\Resources\AcceptsCustomFields;

class EventResource extends Resource implements Resourceful, Tableable, AcceptsCustomFields, Exportable
{
    public static bool $globallySearchable = true;

    public static $useFields;

    public static function swapFields($fields)
    {
        Fields::fresh();
        static::$useFields = $fields;
    }

    public static function repository()
    {
        return resolve(EventRepository::class);
    }

    public function table($repository, Request $request) : Table
    {
        return new EventTable($repository, $request);
    }

    public function ownCriteria() : string
    {
        return OwnEventsCriteria::class;
    }

    public function fields(Request $request) : array
    {
        if (static::$useFields) {
            return static::$useFields;
        }

        return [
            Text::make('title', 'Title'),
            Editor::make('description', 'Description'),
            Boolean::make('is_all_day', 'All Day'),
            Date::make('date', 'Date'),
            DateTime::make('start', 'Start'),
            DateTime::make('end', 'End'),
            Number::make('total_guests', 'Total Guests'),
            User::make()->setJsonResource(UserResource::class),
            BelongsTo::make('status', EventStatusRepository::class, 'Status'),
            LocationField::make('locations', 'Locations')->excludeFromExport(),
        ];
    }

    public function filters(ResourceRequest $request) : array
    {
        return [
            TextFilter::make('title', 'Title'),
        ];
    }

    public static function label() : string
    {
        return 'Events';
    }

    public static function singularLabel() : string
    {
        return 'Event';
    }

    public static function name() : string
    {
        return 'events';
    }

    public static function singularName() : string
    {
        return 'event';
    }

    public function associateableName() : string
    {
        return 'events';
    }

    public function jsonResource() : string
    {
        return EventJsonResource::class;
    }

    public function registerPermissions() : void
    {
        $this->registerCommonPermissions();
    }
}
