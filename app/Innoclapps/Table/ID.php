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

class ID extends Column
{
    /**
     * Initialize ID class
     *
     * @param string|null $label
     * @param string|null $attribute
     */
    public function __construct(?string $label = null, ?string $attribute = 'id')
    {
        parent::__construct($attribute, $label);

        $this->minWidth('120px')->width('120px');
    }
}
