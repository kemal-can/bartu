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

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Http\Resources\CountryResource;
use App\Innoclapps\Contracts\Repositories\CountryRepository;

class Country extends BelongsTo
{
    /**
     * Create new instance of Country field
     *
     * @param string $label Custom label
     */
    public function __construct($label = null)
    {
        parent::__construct('country', CountryRepository::class, $label ?? __('country.country'));

        $this->acceptLabelAsValue(false)->setJsonResource(CountryResource::class);
    }

    /**
     * Get the field value when label is provided
     *
     * @param string $value
     * @param array $input
     *
     * @return int|null
     */
    protected function parseValueAsLabelViaOptionable($value, $input)
    {
        $options = $this->getCachedOptionsCollection();

        return $options->first(function ($country) use ($value) {
            return Str::is($country->name, $value) ||
                Str::is($country->iso_3166_2, $value) ||
                Str::is($country->iso_3166_3, $value) ||
                Str::contains($country->full_name, $value);
        })[$this->valueKey] ?? null;
    }

    /**
     * Get cached options collection
     *
     * When importing data, the label as value function will be called
     * multiple times, we don't want all the queries executed multiple times
     * from the fields which are providing options via repository
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCachedOptionsCollection() : Collection
    {
        if (! $this->cachedOptions) {
            $this->cachedOptions = $this->repository->all();
        }

        return $this->cachedOptions;
    }

    /**
     * Resolve the field value for import
     *
     * @param string|null $value
     * @param array $row
     * @param array $original
     *
     * @return array
     */
    public function resolveForImport($value, $row, $original)
    {
        // If not found via label option, will be null as
        // country cannot be created during import
        return [$this->attribute => $value];
    }
}
