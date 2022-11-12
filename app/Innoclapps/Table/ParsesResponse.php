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

use Closure;
use App\Innoclapps\Contracts\Presentable;

trait ParsesResponse
{
    /**
     * Parse the response for the request
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $result
     * @param int $allTimeTotal
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function parseResponse($result, $allTimeTotal)
    {
        $columns = $this->getUserColumns()->reject(fn ($column) => $column->isHidden())->all();

        $result->getCollection()->transform(function ($model) use ($columns) {
            $displayAs = [];

            // Global main table models appends
            $model->append(
                array_merge($this->appends(), $model instanceof Presentable ? ['path'] : [])
            );

            foreach ($columns as $column) {
                // Check for custom appends for relation the models
                if ($column->isRelation()) {
                    $this->appendAttributesWhenRelation($column, $model);
                }

                // Inline displayAs closure provided
                if ($column->displayAs instanceof Closure) {
                    data_set($displayAs, $column->attribute, call_user_func_array($column->displayAs, [$model]));
                }

                // Vuejs casts 0 as empty and 0 will be shown as empty
                elseif ($column->isCountable() && $column->counts()) {
                    data_set($displayAs, $column->attribute, (string) $model->{$column->attribute});
                }
            }

            // Set the model displayAs attribute so it can be serialized for the front-end
            $model->setAttribute('displayAs', $displayAs);

            // If any authorizations, set them as attribute so they can be serialized for the front-end
            if ($authorizations = $this->getAuthorizations($model)) {
                $model->setAttribute('authorizations', $authorizations);
            }

            return $model;
        });

        return (new LengthAwarePaginator(
            $result->getCollection(),
            $result->total(),
            $result->perPage(),
            $result->currentPage()
        ))->setAllTimeTotal($allTimeTotal);
    }

    /**
     * Append attributes when column is relation
     *
     * @param \App\Innoclapps\Table\Column $column
     * @param \Illumiante\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function appendAttributesWhenRelation($column, $model)
    {
        if ($column instanceof BelongsToColumn && $model->{$column->relationName}) {
            $this->appendAttributesWhenBelongsTo($column, $model);
        } elseif ($column instanceof HasManyColumn && ! $column->counts()) {
            $this->appendAttributesWhenHasMany($column, $model);
        }
    }

    /**
     * Append attributes when BelongsToColumn
     *
     * @param \App\Innoclapps\Table\BelongsToColumn $column
     * @param \Illumiante\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function appendAttributesWhenBelongsTo($column, $model)
    {
        $model->{$column->relationName}->append(array_merge(
            $column->appends,
            $model->{$column->relationName}()->getModel() instanceof Presentable ? ['path', 'display_name'] : []
        ));
    }

    /**
     * Append attributes when HasManyColumn
     *
     * @param \App\Innoclapps\Table\HasManyColumn $column
     * @param \Illumiante\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function appendAttributesWhenHasMany($column, $model)
    {
        $model->{$column->relationName}->map(function ($relationModel) use ($column) {
            unset($relationModel->pivot);

            return $relationModel->append(array_merge(
                $column->appends,
                $relationModel instanceof Presentable ? ['path', 'display_name'] : []
            ));
        });
    }
}
