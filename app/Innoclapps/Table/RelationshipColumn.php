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

class RelationshipColumn extends Column
{
    /**
     * Attributes to append with the response
     *
     * @var array
     */
    public $appends = [];

    /**
     * The relation name
     *
     * @var string
     */
    public $relationName;

    /**
     * The relation field
     *
     * @var string
     */
    public $relationField;

    /**
     * Additional fields to select
     *
     * @see @method select
     *
     * @var array
     */
    public $relationSelectColumns = [];

    /**
     * Initialize new RelationshipColumn instance.
     *
     * @param string $name
     * @param string $field
     * @param string|null $label
     */
    public function __construct($name, ?string $attribute, ?string $label = null)
    {
        // The relation names for front-end are returned in snake case format.
        parent::__construct(Str::snake($name), $label);

        $this->relationName  = $name;
        $this->relationField = $attribute;
    }

    /**
     * Additional select for a relation
     *
     * For relation e.q. MorphToManyColumn::make('contacts', 'first_name', 'Contacts')->select(['avatar', 'email'])
     *
     * @param array|string $fields
     *
     * @return static
     */
    public function select(array|string $fields) : static
    {
        $this->relationSelectColumns = array_merge(
            $this->relationSelectColumns,
            (array) $fields
        );

        return $this;
    }

    /**
     * Set attributes to appends in the model
     *
     * @param array|string $attributes
     *
     * @return static
     */
    public function appends(array|string $attributes) : static
    {
        $this->appends = (array) $attributes;

        return $this;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'relationField' => $this->relationField,
        ]);
    }
}
