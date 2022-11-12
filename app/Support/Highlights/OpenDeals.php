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

namespace App\Support\Highlights;

use App\Criteria\Deal\OwnDealsCriteria;
use App\Criteria\Deal\OpenDealsCriteria;
use App\Contracts\Repositories\DealRepository;
use App\Innoclapps\Contracts\Repositories\FilterRepository;

class OpenDeals extends Highlight
{
    /**
    * @var \App\Contracts\Repositories\DealRepository
    */
    protected DealRepository $repository;

    /**
    * @var \App\Innoclapps\Contracts\Repositories\FilterRepository
    */
    protected FilterRepository $filterRepository;

    /**
    * Initialize new OpenDeals instance.
    */
    public function __construct()
    {
        $this->repository       = app(DealRepository::class);
        $this->filterRepository = app(FilterRepository::class);
    }

    /**
    * Get the highlight name
    *
    * @return string
    */
    public function name() : string
    {
        return __('deal.highlights.open');
    }

    /**
    * Get the highligh count
    *
    * @return integer
    */
    public function count() : int
    {
        return $this->repository->pushCriteria(OwnDealsCriteria::class)
            ->pushCriteria(OpenDealsCriteria::class)
            ->count();
    }

    /**
    * Get the background color class when the highligh count is bigger then zero
    *
    * @return string
    */
    public function bgColorClass() : string
    {
        return 'bg-info-500';
    }

    /**
    * Get the router to
    *
    * @return array|string
    */
    public function to() : array|string
    {
        $filter = $this->filterRepository->findByFlag('open-deals');

        return [
            'name'  => 'deal-index',
            'query' => [
                'filter_id' => $filter?->id,
            ],
        ];
    }
}
