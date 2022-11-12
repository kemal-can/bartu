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

namespace App\Innoclapps;

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractMask implements JsonSerializable, Arrayable
{
    /**
     * Initialize the mask
     *
     * @param array|object $entity
     */
    public function __construct(protected $entity)
    {
    }

    /**
     * Get the entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
