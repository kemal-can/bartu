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

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Innoclapps\ResolvesActions;
use App\Innoclapps\ResolvesFilters;
use App\Innoclapps\Contracts\Countable;
use App\Innoclapps\Repository\BaseRepository;
use App\Innoclapps\ProvidesModelAuthorizations;
use App\Innoclapps\Criteria\FilterRulesCriteria;
use App\Innoclapps\Criteria\TableRequestCriteria;
use App\Innoclapps\Resources\Http\ResourceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Table
{
    use ParsesResponse,
        ResolvesFilters,
        ResolvesActions,
        HandlesRelations,
        ProvidesModelAuthorizations;

    /**
     * Additional relations to eager load on every query.
     *
     * @var array
     */
    protected array $with = [];

    /**
     * Additional countable relations to eager load on every query.
     *
     * @var array
     */
    protected array $withCount = [];

    /**
     * Custom table filters
     *
     * @var \Illuminate\Support\Collection|array|null
     */
    protected $filters;

    /**
     * Custom table actions
     *
     * @var \Illuminate\Support\Collection|array|null
     */
    protected $actions;

    /**
     * Table identifier
     *
     * @var string
     */
    protected string $identifier;

    /**
     * Additional request query string for the table request
     *
     * @var array
     */
    public array $requestQueryString = [];

    /**
     * Table order
     *
     * @var array
     */
    public array $order = [];

    /**
     * Table default per page value
     *
     * @var integer
     */
    public int $perPage = 25;

    /**
     * Whether the table columns can be customized.
     * You must ensure all columns has unique ID's before setting this properoty to true
     *
     * @var boolean
     */
    public bool $customizeable = false;

    /**
     * Whether the table sorting options can be changed.
     * Only works if $customizeable is set to true
     *
     * @var boolean
     */
    public bool $allowDefaultSortChange = true;

    /**
     * Whether the table has actions column
     *
     * @var boolean
     */
    public bool $withActionsColumn = false;

    /**
     * Table max height
     *
     * @var int|null
     */
    public $maxHeight = null;

    /**
     * The repository original model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = null;

    /**
     * @var \App\Innoclapps\Table\TableSettings
     */
    protected TableSettings $settings;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected Collection $columns;

    /**
     * Initialize new Table instance.
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     */
    public function __construct(protected BaseRepository $repository, protected ResourceRequest $request)
    {
        $this->model = $this->repository->getModel();
        $this->setIdentifier(Str::kebab(class_basename(static::class)));
        $this->setColumns($this->columns());
        $this->settings = new TableSettings(
            $this,
            $this->request->user(),
        );
        $this->boot();
    }

    /**
     * Custom boot method
     */
    public function boot() : void
    {
        //
    }

    /**
     * Provides table columns
     *
     * @return array
     */
    public function columns() : array
    {
        return [];
    }

    /**
     * Set the table column
     *
     * @param array $columns
     *
     * @return static
     */
    public function setColumns(array $columns) : static
    {
        $this->columns = new Collection($columns);

        if ($this->withActionsColumn === true) {
            // Check if we need to add the action
            if (! $this->columns->whereInstanceOf(ActionColumn::class)->first()) {
                $this->addColumn(new ActionColumn);
            }
        }

        return $this;
    }

    /**
     * Add new column to the table
     *
     * @param \App\Innoclapps\Table\Column $column
     *
     * @return static
     */
    public function addColumn(Column $column) : static
    {
        $this->columns->push($column);

        return $this;
    }

    /**
     * Creates the table data and return the data
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function make() : LengthAwarePaginator
    {
        // We will count the all time total before any filters and criterias
        $scope        = $this->repository->getScope();
        $allTimeTotal = $this->repository->resetScope()->skipCriteria()->count();
        $this->repository->skipCriteria(false);

        if ($scope) {
            $this->repository->scopeQuery($scope);
        }

        $this->repository->pushCriteria(new TableRequestCriteria(
            $this->request,
            $this->getUserColumns(),
            $this
        ));

        $this->applyRules();
        $this->setSearchableFields();

        // If you're combining withCount with a select statement,
        // ensure that you call withCount after the select method
        $response = $this->repository->columns($this->getSelectColumns())
            ->with(array_merge($this->withRelationships(), $this->with))
            ->withCount(array_merge($this->countedRelationships(), $this->withCount))
            ->paginate((int) $this->request->get('per_page', $this->perPage));

        return $this->parseResponse($response, $allTimeTotal);
    }

    /**
     * Get the table request
     *
     * @return \App\Innoclapps\Resources\Http\ResourceRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the server for the table AJAX request params
     *
     * @return array
     */
    public function getRequestQueryString() : array
    {
        return $this->requestQueryString;
    }

    /**
     * Set table default order by
     *
     * @param string $attribute
     * @param string $dir asc|desc
     *
     * @return static
     */
    public function orderBy(string $attribute, string $dir = 'asc') : static
    {
        $this->order[] = ['attribute' => $attribute, 'direction' => $dir];

        return $this;
    }

    /**
     * Clear the order by attributes
     *
     * @return static
     */
    public function clearOrderBy() : static
    {
        $this->order = [];

        return $this;
    }

    /**
     * Add additional relations to eager load
     *
     * @param string|array $relations
     *
     * @return static
     */
    public function with(string|array $relations) : static
    {
        $this->with = array_merge($this->with, (array) $relations);

        return $this;
    }

    /**
     * Add additional countable relations to eager load
     *
     * @param string|array $relations
     *
     * @return static
     */
    public function withCount(string|array $relations) : static
    {
        $this->withCount = array_merge($this->withCount, (array) $relations);

        return $this;
    }

    /**
     * Get the table available table filters
     * Checks for custom configured filters
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function filters(ResourceRequest $request) : array|Collection
    {
        return $this->filters ?? [];
    }

    /**
     * Set table available filters
     *
     * @param array|\Illuminate\Support\Collection $filters
     *
     * @return static
     */
    public function setFilters(array|Collection $filters) : static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set the table available actions
     *
     * @param array|\Illuminate\Support\Collection $actions
     *
     * @return static
     */
    public function setActions(array|Collection $actions) : static
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Available table actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function actions(ResourceRequest $request) : array|Collection
    {
        return $this->actions ?? [];
    }

    /**
     * Get defined column by given attribute
     *
     * @param string $attribute
     *
     * @return \App\Innoclapps\Table\Column|null
     */
    public function getColumn(string $attribute) : ?Column
    {
        return $this->columns->firstWhere('attribute', $attribute);
    }

    /**
     * Get table available columns
     *
     * @see \App\Innoclapps\Table\CustomizesTable
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumns() : Collection
    {
        return $this->columns;
    }

    /**
    * Check if the table is sorted by specific column
    *
    * @param \App\Innoclapps\Table\Column  $column
    *
    * @return boolean
    */
    public function isSortingByColumn(Column $column) : bool
    {
        $sortingBy      = $this->request->get('order', []);
        $sortedByFields = data_get($sortingBy, '*.attribute');

        return in_array($column->attribute, $sortedByFields);
    }

    /**
     * Get the table settings for the given request
     *
     * @return \App\Innoclapps\Table\TableSettings
     */
    public function settings() : TableSettings
    {
        return $this->settings;
    }

    /**
     * Get the table identifier
     *
     * @return string
     */
    public function identifier() : string
    {
        return $this->identifier;
    }

    /**
     * Set table identifier
     *
     * @param string $key
     *
     * @return static
     */
    public function setIdentifier(string $key) : static
    {
        $this->identifier = $key;

        return $this;
    }

    /**
     * Get additional select columns for the query
     *
     * @return array
     */
    protected function addSelect() : array
    {
        return [];
    }

    /**
     * Append mode attributes
     *
     * @return array
     */
    protected function appends() : array
    {
        return [];
    }

    /**
     * Get select columns
     *
     * Will return that columns only that are needed for the table
     * For example of the user made some columns not visible they won't be queried
     *
     * @return array
     */
    protected function getSelectColumns() : array
    {
        $columns = $this->getUserColumns();
        $select  = [];

        foreach ($columns as $column) {
            if ($column->isHidden() && ! $column->queryWhenHidden) {
                continue;
            }

            if (! $column->isRelation()) {
                if ($field = $this->getSelectableField($column)) {
                    $select[] = $field;
                }
            } elseif ($column instanceof BelongsToColumn) {
                // Select the foreign key name for the BelongsToColumn
                // If not selected, the relation won't be queried properly
                $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
            }
        }

        return array_unique(array_merge(
            $this->qualifyColumn($this->addSelect()),
            [$this->model->getQualifiedKeyName() . ' as ' . $this->model->getKeyName()],
            $select
        ));
    }

    /**
     * Set the repository searchable fields based on the
     * visible columns
     *
     * @return void
     */
    protected function setSearchableFields() : void
    {
        $this->repository->setSearchableFields($this->getSearchableColumns()->mapWithKeys(function ($column) {
            if ($column->isRelation()) {
                $searchableField = $column->relationName . '.' . $column->relationField;
            } else {
                $searchableField = $column->attribute;
            }

            return [$searchableField => 'like'];
        })->all());
    }

    /**
     * Filter the searchable columns
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getSearchableColumns() : Collection
    {
        return $this->getUserColumns()->filter(function ($column) {
            // We will check if the column is date column, as date columns are not searchable
            // as there won't be accurate results because the database dates are stored in UTC timezone
            // In this case, the filters must be used
            // Additionally we will check if is countable column and the column counts
            if ($column instanceof DateTimeColumn ||
                $column instanceof DateColumn ||
                $column instanceof Countable && $column->counts()) {
                return false;
            }

            // Relation columns with no custom query are searchable
            if ($column->isRelation()) {
                return empty($column->queryAs);
            }

            // Regular database, and also is not queried
            // with DB::raw, when querying with DB::raw, you must implement
            // custom searching criteria
            return empty($column->queryAs);
        });
    }

    /**
     * Apply table rules/filters
     *
     * @return void
     */
    protected function applyRules() : void
    {
        $this->repository->pushCriteria(new FilterRulesCriteria(
            $this->request->get('rules'),
            $this->resolveFilters($this->request),
            $this->request
        ));
    }

    /**
     * Get field by column that should be included in the table select query
     *
     * @see  For $isRelationWith take a look in \App\Innoclapps\Table\HandlesRelations
     *
     * @param \App\Innoclapps\Table\Column $column
     * @param boolean $isRelationWith Whether this field will be used for eager loading
     *
     * @return mixed
     */
    protected function getSelectableField(Column $column, $isRelationWith = false)
    {
        if ($column instanceof ActionColumn) {
            return null;
        }

        if (! empty($column->queryAs)) {
            return $column->queryAs;
        } elseif ($isRelationWith) {
            return $this->qualifyColumn($column->relationField, $column->relationName);
        }

        return $this->qualifyColumn($column->attribute);
    }

    /**
     * Qualify the given column
     *
     * @param string[] $column
     * @param string|null $relationName
     * @return mixed
     */
    protected function qualifyColumn($column, $relationName = null)
    {
        if (is_array($column)) {
            return array_map(fn ($column) => $this->qualifyColumn($column, $relationName), $column);
        }

        if ($relationName) {
            return $this->model->{$relationName}()->qualifyColumn($column);
        }

        return $this->model->qualifyColumn($column);
    }

    /**
     * Get the columns for the table intended to be shown to the logged in user
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getUserColumns() : Collection
    {
        return $this->settings()->getColumns();
    }
}
