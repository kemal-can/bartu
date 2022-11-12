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

use Akaunting\Money\Money;
use Akaunting\Money\Currency;
use App\Innoclapps\Facades\Innoclapps;

class NumericColumn extends Column
{
    /**
     * Indicates whether the field has currency
     *
     * @var null|\Akaunting\Money\Currency
     */
    public null|Currency $currency = null;

    /**
     * Initialize new NumericColumn instance.
     *
     * @param array $params
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        // Do not use queryAs as it's not supported (tested) for this type of column
        $this->displayAs(function ($model) {
            $value = $model->{$this->attribute};

            if (empty($value)) {
                return '--';
            }

            if (! $this->currency) {
                return $value;
            }

            return (new Money((float) $value, $this->currency, true))->format();
        });
    }

    /**
     * Set that the value should be displayed with currency
     *
     * @param string|null|\Akaunting\Money\Currency|null $currency
     *
     * @return static
     */
    public function currency(string|null|Currency $currency = null) : static
    {
        if (is_string($currency) || is_null($currency)) {
            $currency = new Currency($currency ?: Innoclapps::currency());
        }

        $this->currency = $currency;

        return $this;
    }
}
