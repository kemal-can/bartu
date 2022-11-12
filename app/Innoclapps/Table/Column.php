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
use JsonSerializable;
use App\Innoclapps\Makeable;
use App\Innoclapps\Authorizeable;
use App\Innoclapps\MetableElement;
use App\Innoclapps\Contracts\Countable;
use Illuminate\Database\Query\Expression;
use Illuminate\Contracts\Support\Arrayable;

class Column implements Arrayable, JsonSerializable
{
    use Authorizeable,
        MetableElement,
        Makeable;

    /**
     * Custom query for this field
     *
     * @var mixed
     */
    public $queryAs;

    /**
     * Indicates whether the column is sortable
     *
     * @var boolean
     */
    public bool $sortable = true;

    /**
     * Indicates whether to add the column contents in the front end as HTML
     *
     * @var boolean
     */
    public bool $asHtml = false;

    /**
     * Indicates whether the column is hidden
     *
     * @var boolean
     */
    public ?bool $hidden = null;

    /**
     * Indicates special columns and some actions are disabled
     *
     * @var boolean
     */
    public bool $primary = false;

    /**
     * Table th/td min width
     *
     * @var string
     */
    public string $minWidth = '200px';

    /**
     * The column default order
     *
     * @var null
     */
    public ?int $order = null;

    /**
     * Indicates whether to include the column in the query when it's hidden
     *
     * @var boolean
     */
    public bool $queryWhenHidden = false;

    /**
     * Custom column display formatter
     *
     * @var \Closure|null
     */
    public ?Closure $displayAs = null;

    /**
     * Column help text
     *
     * @var string|null
     */
    public ?string $helpText = null;

    /**
     * Data heading component
     *
     * @var string
     */
    public string $component = 'table-data-column';

    /**
     * Initialize new Column instance.
     *
     * @param string|null $attribute
     * @param string|null $label
     */
    public function __construct(public ?string $attribute = null, public ?string $label = null)
    {
    }

    /**
     * Custom query for this column
     *
     * @param \Illuminate\Database\Query\Expression|string|\Closure $queryAs
     *
     * @return static
     */
    public function queryAs(Expression|Closure|string $queryAs) : static
    {
        $this->queryAs = $queryAs;

        return $this;
    }

    /**
     * Set column name/label
     *
     * @param string $label
     *
     * @return static
     */
    public function label(?string $label) : static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set whether the column is sortable
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function sortable(bool $bool = true) : static
    {
        $this->sortable = $bool;

        return $this;
    }

    /**
     * Set the column help text
     *
     * @param string $text
     *
     * @return static
     */
    public function help(?string $text) : static
    {
        $this->helpText = $text;

        return $this;
    }

    /**
     * Check whether the column is sortable
     *
     * @return boolean
     */
    public function isSortable() : bool
    {
        return $this->sortable === true;
    }

    /**
     * Add the column content as HTML
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function asHtml(bool $bool = true) : static
    {
        $this->asHtml = $bool;

        return $this;
    }

    /**
     * Set column visibility
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function hidden(bool $bool = true) : static
    {
        $this->hidden = $bool;

        return $this;
    }

    /**
     * Check whether the column is hidden
     *
     * @return boolean
     */
    public function isHidden() : bool
    {
        return $this->hidden === true;
    }

    /**
     * Set the column as primary
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function primary(bool $bool = true) : static
    {
        $this->primary = $bool;

        return $this;
    }

    /**
     * Check whether the column is primary
     *
     * @return boolean
     */
    public function isPrimary() : bool
    {
        return $this->primary === true;
    }

    /**
     * Set column td custom class
     *
     * @param string $class
     *
     * @return static
     */
    public function tdClass(string $class) : static
    {
        return $this->withMeta([__FUNCTION__ => $class]);
    }

    /**
     * Set column th custom class
     *
     * @param string $class
     *
     * @return static
     */
    public function thClass(string $class) : static
    {
        return $this->withMeta([__FUNCTION__ => $class]);
    }

    /**
     * Set column th/td width
     *
     * @param string $width
     *
     * @return static
     */
    public function width($width) : static
    {
        return $this->withMeta([__FUNCTION__ => $width]);
    }

    /**
     * Set column th/td min width
     *
     * @param string $minWidth
     *
     * @return static
     */
    public function minWidth(string $minWidth) : static
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * Set the column default order
     *
     * @param int $order
     *
     * @return static
     */
    public function order(int $order) : static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Whether to select/query the column when the column hidden
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function queryWhenHidden(bool $bool = true) : static
    {
        $this->queryWhenHidden = $bool;

        return $this;
    }

    /**
     * Custom column formatter
     *
     * @param \Closure $callback
     *
     * @return static
     */
    public function displayAs(Closure $callback) : static
    {
        $this->displayAs = $callback;

        return $this;
    }

    /**
     * Check whether a column can count relations
     *
     * @return boolean
     */
    public function isCountable() : bool
    {
        return $this instanceof Countable;
    }

    /**
     * Get the column data component
     *
     * @return string
     */
    public function component() : string
    {
        return $this->component;
    }

    /**
     * Set the column data component
     *
     * @param string $component
     *
     * @return static
     */
    public function useComponent(string $component) : static
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Check whether the column is a relation
     *
     * @return boolean
     */
    public function isRelation() : bool
    {
        return $this instanceof RelationshipColumn;
    }

    /**
     * Center the column heading and data
     *
     * @return static
     */
    public function centered() : static
    {
        return $this->thClass('text-center')->tdClass('text-center');
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge([
            'attribute' => $this->attribute,
            'label'     => $this->label,
            'sortable'  => $this->isSortable(),
            'asHtml'    => $this->asHtml,
            'hidden'    => $this->isHidden(),
            'primary'   => $this->isPrimary(),
            'component' => $this->component(),
            'minWidth'  => $this->minWidth,
            'order'     => $this->order,
            'helpText'  => $this->helpText,
            // 'isCountable' => $this->isCountable(),
        ], $this->meta());
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
