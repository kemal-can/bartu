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

namespace App\Innoclapps\Table;

use JsonSerializable;
use Illuminate\Support\Collection;
use App\Innoclapps\Contracts\Metable;
use App\Http\Resources\FilterResource;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Contracts\Support\Arrayable;
use App\Innoclapps\Contracts\Repositories\FilterRepository;
use App\Innoclapps\Table\Exceptions\OrderByNonExistingColumnException;

class TableSettings implements Arrayable, JsonSerializable
{
    /**
    * Holds the partially user meta key for the saved table settings
    */
    protected string $meta = 'table-settings';

    /**
     * @var \App\Innoclapps\Contracts\Repositories\FilterRepository
     */
    protected FilterRepository $filters;

    /**
     * @var \Illumiante\Support\Collection
     */
    protected $columns;

    /**
     * TableSettings Construct
     *
     * @param \App\Innoclapps\Table\Table $table
     * @param \App\Innoclapps\Contracts\Metable $user The user the data/settings are intended for
     */
    public function __construct(protected Table $table, protected Metable $user)
    {
        $this->filters = resolve(FilterRepository::class);
    }

    /**
     * Table actions
     *
     * The function removes also the actions that are hidden on INDEX
     *
     * @return \Illuminate\Support\Collection
     */
    public function actions() : Collection
    {
        return $this->table->resolveActions($this->table->getRequest())
            ->reject(fn ($action) => $action->hideOnIndex === true)
            ->values();
    }

    /**
     * Get the available table saved filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function savedFilters() : Collection
    {
        return $this->filters->orderBy('name')
            ->forUser($this->table->identifier(), $this->user->id);
    }

    /**
     * Get the table max height
     *
     * @return float|int|string|null
     */
    public function maxHeight() : float|int|string|null
    {
        return $this->getCustomizedSettings()['maxHeight'] ?? $this->table->maxHeight;
    }

    /**
     * Get the table per page
     *
     * @return int
     */
    public function perPage() : int
    {
        return $this->getCustomizedSettings()['perPage'] ?? $this->table->perPage;
    }

    /**
     * Saves customized table data
     *
     * @param array|null $data
     *
     * @return static
     */
    public function update(?array $data) : static
    {
        // Protect the primary fields visibility when
        // direct API request is performed
        if (! empty($data)) {
            $this->guardColumns($data);
        }

        $this->user->setMeta($this->getMetaName(), $data);
        $this->user = Innoclapps::getUserRepository()->find($this->user->getKey());

        return $this;
    }

    /**
     * Get the user columns meta name
     *
     * @return string
     */
    public function getMetaName() : string
    {
        return $this->meta . '-' . $this->table->identifier();
    }

    /**
     * Get table order, checks for custom ordering too
     *
     * @return array
     */
    public function getOrder() : array
    {
        $customizedOrder = $this->getCustomizedOrder();

        if (count($customizedOrder) === 0) {
            return $this->table->order;
        }

        return collect($customizedOrder)->reject(function ($data) {
            // Check and unset the custom ordered field in case no longer exists as available columns
            // For example it can happen a database change and this column is no longer available,
            // for this reason we must not sort by this column because it may be removed from database
            return is_null($this->table->getColumn($data['attribute']));
        })->values()->all();
    }

    /**
      * Validate the order
      *
      * @param array $order
      * @throws \App\Innoclapps\Table\Exceptions\OrderByNonExistingColumnException
      *
      * @return array
      */
    protected function validateOrder(array $order) : array
    {
        foreach ($order as $data) {
            throw_if(
                is_null($this->table->getColumn($data['attribute'])),
                new OrderByNonExistingColumnException($data['attribute'])
            );
        }

        return $order;
    }

    /**
     * Get the actual table columns that should be shown to the user
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumns() : Collection
    {
        if ($this->columns) {
            return $this->columns;
        }

        $customizedColumns = collect($this->getCustomizedColumns());
        $availableColumns  = $this->table->getColumns()->filter->authorizedToSee()->values();

        // Merge the order and the visibility and all columns so we can filter them later
        $columns = $availableColumns->map(function ($column, $index) use ($customizedColumns) {
            if ($column instanceof ActionColumn) {
                $column->order(1000)->hidden(false);
            } else {
                $customizedColumn = $customizedColumns->firstWhere('attribute', $column->attribute);
                $column->order($customizedColumn ? (int) $customizedColumn['order'] : $index + 1);
                // We will first check if the column has customization applied, if yes, we will
                // use the option from the customization otherwise column is not yet in customized columns, perhaps is added later
                // either set the visibility if defined from the column config or set it default true if not defined
                $column->hidden(
                    $customizedColumn ? ($customizedColumn['hidden'] ?? false) : ($column->hidden ?? false)
                );
            }

            return $column;
        });

        return $this->columns = $columns->sortBy('order')->values();
    }

    /**
     * Get user customized table data that is stored in database/meta
     *
     * @return array|null
     */
    public function getCustomizedSettings() : ?array
    {
        return $this->user->getMeta($this->getMetaName());
    }

    /**
     * Get table customized user
     *
     * @return array
     */
    public function getCustomizedOrder() : array
    {
        return $this->getCustomizedSettings()['order'] ?? [];
    }

    /**
     * Get table customized columns
     *
     * @return array
     */
    public function getCustomizedColumns() : array
    {
        return $this->getCustomizedSettings()['columns'] ?? [];
    }

    /**
     * Guard the primary and not sortable columns
     *
     * @param array $payload
     *
     * @return void
     */
    protected function guardColumns(&$payload) : void
    {
        $this->guardPrimaryColumns($payload);
        $this->guardNotSortableColumns($payload);
    }

    /**
     * Guards the primary fields from mole changes via API
     *
     * @param array $payload
     *
     * @return void
     */
    protected function guardPrimaryColumns(&$payload) : void
    {
        // Protected the primary fields hidden option
        // when direct API request
        // e.q. the field attribute hidden is set to false when it must be visible
        // because the field is marked as primary field
        foreach ($payload['columns'] as $key => $column) {
            $column = $this->table->getColumn($column['attribute']);

            // Reset with the default attributes for additional protection
            if ($column->isPrimary()) {
                $payload['columns'][$key]['hidden'] = $column->isHidden();
                $payload['columns'][$key]['order']  = $column->order;
            }
        }
    }

    /**
     * Guards the not sortable columns from mole changes via API
     *
     * @param array $payload
     *
     * @return void
     */
    protected function guardNotSortableColumns(&$payload) : void
    {
        // Protected the not sortable columns
        // E.q. if column is marked to be not sortable
        // The user is not allowed to change to sortable
        foreach ($payload['order'] as $key => $sort) {
            $column = $this->table->getColumn($sort['attribute']);

            // Reset with the default attributes for additional protection
            if (! $column->isSortable()) {
                unset($payload['order'][$key]);
            }
        }
    }

    /**
     * Get the saved filters resource
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filtersResource()
    {
        return FilterResource::collection($this->savedFilters());
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'identifier'             => $this->table->identifier(),
            'perPage'                => $this->perPage(),
            'customizeable'          => $this->table->customizeable,
            'allowDefaultSortChange' => $this->table->allowDefaultSortChange,
            'requestQueryString'     => $this->table->getRequestQueryString(),
            'rules'                  => $this->table->resolveFilters($this->table->getRequest()),
            'maxHeight'              => $this->maxHeight(),
            'filters'                => $this->filtersResource(),
            'columns'                => $this->getColumns(),
            'order'                  => $this->validateOrder($this->getOrder()),
            'actions'                => $this->actions(),
        ];
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
}
