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

namespace App\Resources\Inbox;

use App\Innoclapps\Table\Column;
use App\Innoclapps\Table\HasManyColumn;
use App\Innoclapps\Table\DateTimeColumn;

class OutgoingMessageTable extends IncomingMessageTable
{
    /**
    * Provides table available default columns
    *
    * @return array
    */
    public function columns() : array
    {
        return [
            Column::make('subject', __('inbox.subject')),

            HasManyColumn::make('to', 'address', __('inbox.to'))
                ->select('name'),

            DateTimeColumn::make('date', __('inbox.date')),
        ];
    }
}
