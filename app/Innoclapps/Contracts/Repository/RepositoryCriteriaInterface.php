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

namespace App\Innoclapps\Contracts\Repository;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryCriteriaInterface
 */
interface RepositoryCriteriaInterface
{
    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return static
     */
    public function pushCriteria($criteria);

    /**
     * Pop Criteria
     *
     * @param mixed $criteria
     *
     * @return static
     */
    public function popCriteria($criteria);

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria();

    /**
     * Find data by criteria
     *
     * @param \App\Innoclapps\Contracts\Repository\CriteriaInterface $criteria
     *
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria);

    /**
     * Skip Criteria
     *
     * @param bool $status
     *
     * @return static
     */
    public function skipCriteria($status = true);

    /**
     * Reset all Criterias
     *
     * @return static
     */
    public function resetCriteria();
}
