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

trait MetableElement
{
    /**
     * Additional field meta
     *
     * @var array
     */
    public array $meta = [];

    /**
     * Get the element meta
     *
     * @return array
     */
    public function meta() : array
    {
        return $this->meta;
    }

    /**
     * Add element meta
     *
     * @param array $attributes
     *
     * @return static
     */
    public function withMeta(array $attributes) : static
    {
        $this->meta = array_merge_recursive($this->meta, $attributes);

        return $this;
    }
}
