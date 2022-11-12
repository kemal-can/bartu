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

namespace App\Innoclapps\Filters;

use JsonSerializable;
use Illuminate\Support\Str;
use App\Innoclapps\Makeable;
use App\Innoclapps\Authorizeable;
use App\Innoclapps\MetableElement;
use App\Innoclapps\QueryBuilder\Parser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Arrayable;
use App\Innoclapps\QueryBuilder\ParserTrait;

class Filter implements JsonSerializable, Arrayable
{
    use ParserTrait,
        Authorizeable,
        MetableElement,
        Makeable;

    /**
     * Query builder rule component
     *
     * @var null|string
     */
    public $component = null;

    /**
     * Filter field/rule
     *
     * @var string
     */
    public $field;

    /**
     * Filter label
     *
     * @var string|null
     */
    public $label;

    /**
     * Whether to include null operators
     *
     * @var boolean
     */
    public $withNullOperators = false;

    /**
     * Filter operators
     *
     * @var array
     */
    public $filterOperators = [];

    /**
     * Exclude operators
     *
     * @var array
     */
    public $excludeOperators = [];

    /**
     * @var null|callable
     */
    public $tapCallback;

    /**
     * Indicates whether the filter is static
     *
     * @var boolean
     */
    public $static = false;

    /**
     * @var null|callable
     */
    protected $callback;

    /**
     * Filter current operator
     *
     * @var string|null
     */
    protected $operator;

    /**
     * Filter current value
     *
     * @var array|string|null
     */
    protected $value;

    /**
     * Custom display as text
     *
     * @var string|array|null
     */
    protected $displayAs = null;

    /**
     * @param string $field
     * @param string|null $label
     * @param null|array $operators
     */
    public function __construct($field, $label = null, $operators = null)
    {
        $this->field = $field;
        $this->label = $label;

        is_array($operators) ? $this->operators($operators) : $this->determineOperators();
    }

    /**
     * Filter type from available filter types developed for front end
     *
     * @return string|null
     */
    public function type() : ?string
    {
        return null;
    }

    /**
     * Get the filter component
     *
     * @return string
     */
    public function component() : string
    {
        return $this->component ? $this->component : $this->type() . '-rule';
    }

    /**
     * Set custom operators
     *
     * @param array $operators
     *
     * @return static
     */
    public function operators(array $operators) : static
    {
        $this->filterOperators = $operators;

        return $this;
    }

    /**
     * Exclude the empty operators
     *
     * @return static
     */
    public function withoutEmptyOperators() : static
    {
        $this->withoutOperators(['is_empty', 'is_not_empty']);

        return $this;
    }

    /**
     * Exclude operators
     *
     * @param array $operator
     *
     * @return static
     */
    public function withoutOperators($operator) : static
    {
        $this->excludeOperators = is_array($operator) ? $operator : func_get_args();

        return $this;
    }

    /**
     * Whether to include null operators
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function withNullOperators($bool = true) : static
    {
        $this->withNullOperators = $bool;

        return $this;
    }

    /**
     * Get the filter field
     *
     * @return string
     */
    public function field()
    {
        return $this->field;
    }

    /**
     * Get the filter label
     *
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Add custom query handler instead of using the query builder parser
     *
     * @param callable $callback
     *
     * @return static
     */
    public function query(callable $callback) : static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Add query tap callback
     *
     * @param callable $callback
     *
     * @return static
     */
    public function tapQuery(callable $callback) : static
    {
        $this->tapCallback = $callback;

        return $this;
    }

    /**
     * Apply the filter when custom query callback is provided
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed $value
    *  @param string    $condition
    *  @param array     $sqlOperator
     * @param stdClass $rule
     * @param \App\Innoclapps\QueryBuilder\Parser $parser
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, $value, $condition, $sqlOperator, $rule, Parser $parser)
    {
        return call_user_func(
            $this->callback,
            $builder,
            $value,
            $condition,
            $sqlOperator,
            $rule,
            $parser
        );
    }

    /**
     * Mark the filter as static
     *
     * @return static
     */
    public function asStatic() : static
    {
        $this->static    = true;
        $this->component = 'static-rule';

        return $this;
    }

    /**
     * Add display
     *
     * @param mixed $value
     *
     * @return static
     */
    public function displayAs($value) : static
    {
        $this->displayAs = $value;

        return $this;
    }

    /**
     * Determine whether the filter is static
     *
     * @return boolean
     */
    public function isStatic() : bool
    {
        return $this->static === true;
    }

    /**
    * Check whether the filter is optionable
    *
    * @return boolean
    */
    public function isOptionable() : bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the filter is multi optionable
     *
     * @return boolean
     */
    public function isMultiOptionable() : bool
    {
        return $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Check whether the filter has custom callback
     *
     * @return boolean
     */
    public function hasCustomQuery() : bool
    {
        return ! is_null($this->callback);
    }

    /**
     * Set the filter current value
     *
     * @param string|array $value
     *
     * @return static
     */
    public function setValue($value) : static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the filter active value
     *
     * @return string|array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the filter current operator
     *
     * @param string $operator
     *
     * @return static
     */
    public function setOperator($operator) : static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get the filter current operator
     *
     * @return string|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Create rule able array from the filter
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getBuilderData();
    }

    /**
     * Get the fillter operators
     *
     * @return array
     */
    protected function getOperators()
    {
        $operators = array_unique($this->filterOperators);

        if ($this->withNullOperators === false) {
            $operators = array_diff($operators, ['is_null', 'is_not_null']);
        }

        return array_values(
            array_diff(
                $operators,
                $this->excludeOperators
            )
        );
    }

    /**
     * Get operators options
     *
     * @return array
     */
    protected function operatorsOptions()
    {
        $options = [];
        foreach ($this->getOperators() as $operator) {
            $method = Str::studly(str_replace('.', '_', $operator)) . 'OperatorOptions';

            if (method_exists($this, $method)) {
                $options[$operator] = $this->{$method}() ?: [];
            }
        }

        return $options;
    }

    /**
     * Auto determines the operators on initialize based on ParserTrait
     *
     * @return void
     */
    private function determineOperators()
    {
        foreach ($this->operators as $operator => $data) {
            if (in_array($this->type(), $data['apply_to'])) {
                $this->filterOperators[] = $operator;
            }
        }
    }

    /**
     * Get the filter builder data
     *
     * @return array
     */
    public function getBuilderData()
    {
        return [
            'type'  => 'rule',
            'query' => array_filter([
                'type'     => $this->type(),
                'rule'     => $this->field(),
                'operator' => $this->operator,
                'operand'  => $this instanceof OperandFilter ? $this->operand : null,
                'value'    => $this->value,
            ]),
        ];
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge([
            'id'                => $this->field(),
            'label'             => $this->label(),
            'type'              => $this->type(),
            'operators'         => $this->getOperators(),
            'operatorsOptions'  => $this->operatorsOptions(),
            'component'         => $this->component(),
            'isStatic'          => $this->isStatic(),
            'operands'          => $this instanceof OperandFilter ? $this->getOperands() : [],
            'has_authorization' => $this->hasAuthorization(),
            'display_as'        => $this->displayAs,
        ], $this->meta());
    }
}
