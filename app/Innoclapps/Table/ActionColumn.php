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

class ActionColumn extends Column
{
    /**
     * This column is not sortable
     *
     * @var boolean
     */
    public bool $sortable = false;

    /**
     * Initialize new ActionColumn instance.
     *
     * @param string|null $label
     */
    public function __construct(?string $label = null)
    {
        // Set the attribute to null to prevent showing on re-order table options
        parent::__construct(null, $label);
        $this->width('150px');
    }
}
