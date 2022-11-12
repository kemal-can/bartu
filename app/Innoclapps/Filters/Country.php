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

namespace App\Innoclapps\Filters;

use App\Innoclapps\Contracts\Repositories\CountryRepository;

class Country extends Select
{
    /**
     * Initialize Country filter
     */
    public function __construct()
    {
        parent::__construct('country_id', __('fields.companies.country.name'));

        $this->valueKey('id')->labelKey('name')
            ->options(fn () => $this->countries());
    }

    /**
     * Get the filter countries
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function countries()
    {
        return resolve(CountryRepository::class)->get(['id', 'name'])->all();
    }
}
