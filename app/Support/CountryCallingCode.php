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

namespace App\Support;

use App\Resources\Company\Company;
use App\Resources\Contact\Contact;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Contracts\Repositories\CountryRepository;

class CountryCallingCode
{
    /**
     * @var \Iluminate\Database\Eloquent\Collection
     */
    protected static $countries = null;

    /**
     * Get the phone field calling prefix callback
     *
     * @param \App\Resources\Contact\Contact|\App\Resources\Company\Company $resource
     *
     * @return string|null
     */
    public static function guess(Contact|Company $resource) : ?string
    {
        $country = null;

        if (Innoclapps::isImportInProgress() && $countryId = Import::$currentRequest->country_id) {
            $country = static::findCountry($countryId);
        } elseif ($resource->model) {
            $relation     = $resource instanceof Contact ? 'companies' : 'contacts';
            $relatedModel = $resource->model->{$relation}()->whereNotNull('country_id')->first();
            if ($relatedModel) {
                $country = static::findCountry($relatedModel->country_id);
            }
        }

        return ($country ?: static::getCompanyCountry())?->calling_code;
    }

    /**
     * Get the company country
     *
     * @return \App\Innoclapps\Models\Country|null
     */
    public static function getCompanyCountry()
    {
        if ($countryId = settings('company_country_id')) {
            return static::findCountry($countryId);
        }
    }

    /**
     * Check whether the given number starts with any calling code
     *
     * @param string $number
     *
     * @return boolean
     */
    public static function startsWithAny($number) : bool
    {
        static::loadCountriesInCache();

        return (bool) static::$countries->first(
            fn ($country) => str_starts_with($number, '+' . $country->calling_code)
        );
    }

    /**
     * Get random calling code
     *
     * @return string
     */
    public static function random()
    {
        static::loadCountriesInCache();

        return '+' . static::$countries->random()->calling_code;
    }

    /**
     * Find country by given ID
     *
     * @param int $countryId
     *
     * @return \App\Innoclapps\Models\Country|null
     */
    protected static function findCountry($countryId)
    {
        static::loadCountriesInCache();

        return static::$countries->find($countryId);
    }

    /**
     * Load the counties in cache
     *
     * @return void
     */
    protected static function loadCountriesInCache() : void
    {
        if (! static::$countries) {
            static::$countries = app(CountryRepository::class)->all(['id', 'calling_code']);
        }
    }
}
