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

use Illuminate\Support\Collection;
use App\Innoclapps\Casts\ISO8601Date;
use App\Innoclapps\Casts\ISO8601DateTime;

class CustomFieldResourceCollection extends Collection
{
    /**
     * Fillable attributes cache cache
     *
     * @var array|null
     */
    protected $fillable;

    /**
     * Castable fields cache
     *
     * @var array|null
     */
    protected $castable;

    /**
     * Model casts cache
     *
     * @var array|null
     */
    protected $modelCasts;

    /**
     * Get the optionable fields
     *
     * @return static
     */
    public function optionable()
    {
        return $this->filter->isOptionable();
    }

    /**
     * Get the fillable attributes for the model
     *
     * @return array
     */
    public function fillable()
    {
        if ($this->fillable) {
            return $this->fillable;
        }

        return $this->fillable = $this->filter->isNotMultiOptionable()->pluck('field_id')->all();
    }

    /**
     * Get the model casts
     *
     * @return array
     */
    public function modelCasts()
    {
        if ($this->modelCasts) {
            return $this->modelCasts;
        }

        $data = $this->castableFieldsData();

        return $this->modelCasts = $this->castable()->mapWithKeys(function ($field) use ($data) {
            return [$field->field_id => $data[$field->field_type]];
        })->all();
    }

    /**
     * Get the castable fields
     *
     * @return static
     */
    public function castable()
    {
        if ($this->castable) {
            return $this->castable;
        }

        return $this->castable = $this->whereIn('field_type', array_keys($this->castableFieldsData()));
    }

    /**
     * Get the castable fields data
     *
     * @return array
     */
    protected function castableFieldsData()
    {
        return [
            'Date'     => ISO8601Date::class,
            'DateTime' => ISO8601DateTime::class,
            'Boolean'  => 'boolean',
            'Numeric'  => 'decimal:3',
            'Number'   => 'int',
            'Radio'    => 'int',
            'Select'   => 'int',
        ];
    }
}
