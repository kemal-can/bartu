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

namespace App\Innoclapps\Actions;

class ActionFields
{
    /**
     * Create new instance of action request fields
     *
     * @param array $fields
     */
    public function __construct(protected array $fields)
    {
    }

    /**
     * Get field
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }
}
