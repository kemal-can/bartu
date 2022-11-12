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

namespace App\Innoclapps\Fields;

use App\Innoclapps\Table\Column;

trait DisplaysOnIndex
{
    /**
     * @var callable[]
     */
    public array $tapIndexColumnCallbacks = [];

    /**
     * @var callable
     */
    public $indexColumnCallback;

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\Column
     */
    public function indexColumn() : ?Column
    {
        return new Column($this->attribute, $this->label);
    }

    /**
     * Add custom index column resolver callback
     *
     * @param callable $callback
     *
     * @return static
     */
    public function swapIndexColumn(callable $callback) : static
    {
        $this->indexColumnCallback = $callback;

        return $this;
    }

    /**
     * Tap the index column
     *
     * @param callable $callback
     *
     * @return static
     */
    public function tapIndexColumn(callable $callback) : static
    {
        $this->tapIndexColumnCallbacks[] = $callback;

        return $this;
    }

    /**
     * Resolve the index column
     *
     * @return \App\Innoclapps\Table\Column|null
     */
    public function resolveIndexColumn()
    {
        $column = is_callable($this->indexColumnCallback) ?
                  call_user_func_array($this->indexColumnCallback, [$this]) :
                  $this->indexColumn();

        if (is_null($column)) {
            return null;
        }

        $column->primary($this->isPrimary());
        $column->help($this->helpText);
        $column->hidden(! $this->showOnIndex);

        foreach ($this->tapIndexColumnCallbacks as $callback) {
            tap($column, $callback);
        }

        return $column;
    }
}
