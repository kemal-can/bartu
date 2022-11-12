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

namespace App\Support\Concerns;

use App\Criteria\Deal\OwnDealsCriteria;
use App\Criteria\Deal\WonDealsCriteria;
use App\Criteria\Deal\LostDealsCriteria;
use App\Criteria\Deal\OpenDealsCriteria;
use App\Criteria\Deal\ClosedDealsCriteria;

trait HasDeals
{
    /**
     * Get all of the deals that are associated with the model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function deals()
    {
        return $this->morphToMany(\App\Models\Deal::class, 'dealable');
    }

    /**
     * Initiate query for only deals the user is authorized to see
     *
     * @param callable|null $callback
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function authorizedDeals(?callable $callback = null)
    {
        return $this->deals()->where(function ($query) use ($callback) {
            OwnDealsCriteria::applyQuery($query);

            if ($callback) {
                $callback($query);
            }

            return $query;
        });
    }

    /**
     * Get all of the model open deals that the current logged-in user is authorized to see
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function authorizedOpenDeals()
    {
        return $this->authorizedDeals(function ($query) {
            OpenDealsCriteria::applyQuery($query);
        });
    }

    /**
     * Get all of the model won deals that the current logged-in user is authorized to see
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function authorizedWonDeals()
    {
        return $this->authorizedDeals(function ($query) {
            WonDealsCriteria::applyQuery($query);
        });
    }

    /**
     * Get all of the model closed deals that the current logged-in user is authorized to see
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function authorizedClosedDeals()
    {
        return $this->authorizedDeals(function ($query) {
            ClosedDealsCriteria::applyQuery($query);
        });
    }

    /**
     * Get all of the model lost deals that the current logged-in user is authorized to see
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function authorizedLostDeals()
    {
        return $this->authorizedDeals(function ($query) {
            LostDealsCriteria::applyQuery($query);
        });
    }
}
