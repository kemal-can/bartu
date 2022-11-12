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

use App\Innoclapps\Models\Country;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\CountryRepository;

class CountryRepositoryEloquent extends AppRepository implements CountryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Country::class;
    }
}
