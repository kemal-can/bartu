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

use App\Innoclapps\Models\Import;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\ImportRepository;

class ImportRepositoryEloquent extends AppRepository implements ImportRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Import::class;
    }

    /**
     * Persist the model in storage
     *
     * @param array $attributes
     *
     * @return \App\Innoclapps\Models\Import
     */
    public function create(array $attributes)
    {
        $import = parent::create($attributes);

        $import->loadMissing('user');

        return $import;
    }
}
