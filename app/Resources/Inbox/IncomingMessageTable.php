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

use App\Innoclapps\Table\Table;
use App\Innoclapps\Table\Column;
use App\Innoclapps\Table\HasOneColumn;
use App\Innoclapps\Table\DateTimeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Innoclapps\Filters\Radio as RadioFilter;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Filters\DateTime as DateTimeFilter;

class IncomingMessageTable extends Table
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

            HasOneColumn::make('from', 'address', __('inbox.from'))
                ->select('name'),

            DateTimeColumn::make('date', __('inbox.date')),
        ];
    }

    /**
    * Get the resource available Filters
    *
    * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
    *
    * @return array
    */
    public function filters(ResourceRequest $request) : array
    {
        return [
            TextFilter::make('subject', __('inbox.subject')),

            TextFilter::make('to', __('inbox.to'))->withoutEmptyOperators()
                ->query(function ($builder, $value, $condition, $sqlOperator) {
                    return $builder->whereHas(
                        'from',
                        fn (Builder $query) => $query->where(
                            'address',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )->orWhere(
                            'name',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )
                    );
                }),

            TextFilter::make('from', __('inbox.from'))->withoutEmptyOperators()
                ->query(function ($builder, $value, $condition, $sqlOperator) {
                    return $builder->whereHas(
                        'to',
                        fn (Builder $query) => $query->where(
                            'address',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )->orWhere(
                            'name',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )
                    );
                }),

            DateTimeFilter::make('date', __('inbox.date')),

            RadioFilter::make('is_read', __('inbox.filters.is_read'))->options([
                true  => __('app.yes'),
                false => __('app.no'),
            ]),
        ];
    }

    /**
     * Additional fields to be selected with the query
     *
     * @return array
     */
    public function addSelect() : array
    {
        return [
            'is_read',
            'email_account_id', // uri key for json resource
        ];
    }

    /**
    * Boot table
    *
    * @return null
    */
    public function boot() : void
    {
        // Eager load the folders as the folders are used to create the path
        $this->orderBy('date', 'desc')->with('folders');
    }
}
