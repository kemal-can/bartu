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

class FieldsCollection extends Collection
{
    /**
     * Find field by attribute
     *
     * @param string $attribute
     *
     * @return null\App\Innoclapps\Fields\Field
     */
    public function find($attribute)
    {
        return $this->firstWhere('attribute', $attribute);
    }

    /**
     * Find field by request attribute
     *
     * @param string $attribute
     *
     * @return null\App\Innoclapps\Fields\Field
     */
    public function findByRequestAttribute($attribute)
    {
        return $this->first(function ($field) use ($attribute) {
            return $field->requestAttribute() === $attribute;
        });
    }
}
