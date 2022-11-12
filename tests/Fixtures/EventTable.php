<?php

namespace Tests\Fixtures;

use App\Innoclapps\Table\Table;
use App\Innoclapps\Filters\Text;
use App\Innoclapps\Table\Column;
use App\Innoclapps\Table\BooleanColumn;
use App\Innoclapps\Table\DateTimeColumn;
use App\Innoclapps\Resources\Http\ResourceRequest;

class EventTable extends Table
{
    public function __construct($repository = null, $request = null)
    {
        parent::__construct(
            $repository ?: app(EventRepository::class),
            $request ?: app(ResourceRequest::class)
        );
    }

    public function columns() : array
    {
        return [
            Column::make('title', 'Title'),
            DateTimeColumn::make('start', 'Start Date'),
            DateTimeColumn::make('emd', 'End Date'),
            BooleanColumn::make('is_all_day', 'All Day'),
            Column::make('total_guests', 'Total Guests'),
        ];
    }

    public function filters(ResourceRequest $request) : array
    {
        return [
            Text::make('title', 'Title'),
            Text::make('description', 'Description')->canSee(function () {
                return false;
            }),
        ];
    }
}
