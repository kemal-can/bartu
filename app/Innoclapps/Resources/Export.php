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

use App\Innoclapps\Export\ExportViaFields;
use App\Innoclapps\Repository\AppRepository;

class Export extends ExportViaFields
{
    /**
     * Chunk size
     *
     * @var integer
     */
    public static int $chunkSize = 500;

    /**
     * Create new Export instance.
     *
     * @param \App\Innoclapps\Resources\Resource $resource
     * @param \App\Innoclapps\Repository\AppRepository $repository
     */
    public function __construct(protected Resource $resource, protected AppRepository $repository)
    {
    }

    /**
     * Provides the export data
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        [$with, $withCount] = $this->resource->getEagerloadableRelations($this->fields());

        return $this->repository->withCount($withCount->all())
            ->with($with->all())
            ->lazy(static::$chunkSize);
    }

    /**
    * Provides the resource available fields
    *
    * @return \App\Innoclapps\Fields\FieldsCollection
    */
    public function fields()
    {
        return $this->resource->resolveFields();
    }

    /**
     * The export file name (without extension)
     *
     * @return string
     */
    public function fileName() : string
    {
        return $this->resource->name();
    }
}
