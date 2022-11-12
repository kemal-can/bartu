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

use App\Innoclapps\Facades\Format;

class DateTimeColumn extends Column
{
    /**
     * Initialize new DateTimeColumn instance.
     *
     * @param string|null $attribute
     * @param string|null $label
     */
    public function __construct(?string $attribute = null, ?string $label = null)
    {
        parent::__construct($attribute, $label);

        $this->displayAs(fn ($model) => Format::dateTime($model->{$this->attribute}));
    }
}
