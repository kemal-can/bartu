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

namespace App\Innoclapps\Resources;

use App\Innoclapps\Table\ID;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Table\DateTimeColumn;
use App\Innoclapps\Criteria\OnlyTrashedCriteria;
use App\Innoclapps\Resources\Http\ResourceRequest;

trait ResolvesTables
{
    /**
     * Resolve the resource table class
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function resolveTable(ResourceRequest $request) : Table
    {
        $repository = $this->repository();

        if ($ownCriteria = $this->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        $table = $this->table($repository, $request)->setIdentifier($this->name());

        // If there are no defined columns in the table, we will map
        // the columns from the actual fields

        if (count($table->columns()) === 0) {
            $table->setColumns($this->getTableColumnsFromFields());

            $table->getColumns()->push(
                ID::make(__('app.id'), $repository->getModel()->getKeyName())->hidden()
            );
        }

        // We will check if the tables has table wide actions and filters defined
        // If there are no table wide actions and filters, in this case, we will
        // set the table actions and filters directly from the resource defined.
        if ($table->resolveFilters($request)->isEmpty()) {
            $table->setFilters($this->filtersForResource($request));
        }

        if ($table->resolveActions($request)->isEmpty()) {
            $table->setActions($this->actionsForResource($request));
        }

        return $table;
    }

    /**
     * Resolve the resource trashed table class
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function resolveTrashedTable(ResourceRequest $request) : Table
    {
        $repository = $this->repository()->pushCriteria(OnlyTrashedCriteria::class);

        if ($ownCriteria = $this->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        $table = $this->table($repository, $request)->setIdentifier(
            $this->name() . '-trashed'
        )->clearOrderBy()->orderBy(
            $repository->getModel()->getDeletedAtColumn()
        );

        // Trashed tables are no customizeable
        $table->customizeable = false;

        // OVERWRITE COLUMNS (if any defined)
        // All columns will be visible on the trashed table so the user can see all
        // the data, as well we will push the deleted at column to be visible
        $table->setColumns($this->getTableColumnsFromFields())
            ->getColumns()
            ->prepend(
                DateTimeColumn::make(
                    $repository->getModel()->getDeletedAtColumn(),
                    __('app.deleted_at')
                )
            )
            ->prepend(
                ID::make(__('app.id'), $repository->getModel()->getKeyName())->hidden()
            )
            ->each->hidden(false)->each->primary(false);

        if (method_exists($table, 'actionsForTrashedTable')) {
            $table->setActions($table->actionsForTrashedTable());
        }

        return $table;
    }

    /**
     * Get the table columns from fields
     *
     * @return array
     */
    public function getTableColumnsFromFields() : array
    {
        return $this->resolveFields()->filter->isApplicableForIndex()
            ->map(fn ($field) => $field->resolveIndexColumn())
            ->filter()
            ->all();
    }
}
