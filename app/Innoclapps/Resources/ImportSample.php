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

namespace App\Innoclapps\Resources;

use App\Innoclapps\Import\SampleViaFields;

class ImportSample extends SampleViaFields
{
    /**
     * Create new Import instance
     *
     * @param \App\Innoclapps\Resources\Resource $resource
     */
    public function __construct(protected Resource $resource)
    {
    }

    /**
     * Provides the resource fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function fields()
    {
        return $this->resource->resolveFields();
    }
}
