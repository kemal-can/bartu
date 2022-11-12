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

namespace App\Support\Filters;

use App\Innoclapps\Filters\Text;
use App\Innoclapps\Filters\Country;
use App\Innoclapps\Filters\Operand;
use App\Innoclapps\Filters\OperandFilter;

class AddressOperandFilter extends OperandFilter
{
    /**
     * Initialize the AddressOperandFilter class
     *
     * @param string $resourceName
     */
    public function __construct($resourceName)
    {
        parent::__construct('address', __('app.address'));

        $this->setOperands([
            (new Operand('street', __('fields.' . $resourceName . '.street')))->filter(Text::class),
            (new Operand('city', __('fields.' . $resourceName . '.city')))->filter(Text::class),
            (new Operand('state', __('fields.' . $resourceName . '.state')))->filter(Text::class),
            (new Operand('postal_code', __('fields.' . $resourceName . '.postal_code')))->filter(Text::class),
            (new Operand('country_id', __('fields.' . $resourceName . '.country.name')))->filter(Country::make()),
        ]);
    }
}
