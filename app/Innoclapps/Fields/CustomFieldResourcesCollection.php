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

use Illuminate\Database\Eloquent\Collection;

class CustomFieldResourcesCollection extends Collection
{
    /**
     * Cached resource collection
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Query custom fields for resource
     *
     * @param string $resourceName
     *
     * @return \App\Innoclapps\Fields\CustomFieldResourceCollection
     */
    public function forResource($resourceName)
    {
        if (array_key_exists($resourceName, $this->cache)) {
            return $this->cache[$resourceName];
        }

        return $this->cache[$resourceName] = new CustomFieldResourceCollection(
            $this->where('resource_name', $resourceName)
        );
    }
}
