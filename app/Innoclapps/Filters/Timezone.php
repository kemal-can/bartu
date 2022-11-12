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

use App\Innoclapps\Fields\HasOptions;
use App\Innoclapps\Fields\ChangesKeys;
use App\Innoclapps\Facades\Timezone as Facade;

class Timezone extends Filter
{
    use HasOptions,
        ChangesKeys;

    /**
     * @param string $field
     * @param string|null $label
     * @param null|array $operators
     */
    public function __construct($field, $label = null, $operators = null)
    {
        parent::__construct($field, $label, $operators);

        $this->options(collect(Facade::toArray())->mapWithKeys(function ($timezone) {
            return [$timezone => $timezone];
        })->all());
    }

    /**
     * Defines a filter type
     *
     * @return string
     */
    public function type() : string
    {
        return 'select';
    }
}
