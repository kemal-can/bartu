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

use App\Criteria\Activity\OwnActivitiesCriteria;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\DueTodayActivitiesCriteria;
use App\Innoclapps\Contracts\Repositories\FilterRepository;

class TodaysActivities extends Highlight
{
    /**
    * @var \App\Contracts\Repositories\ActivityRepository
    */
    protected ActivityRepository $repository;

    /**
    * @var \App\Innoclapps\Contracts\Repositories\FilterRepository
    */
    protected FilterRepository $filterRepository;

    /**
    * Initialize new OpenDeals instance.
    */
    public function __construct()
    {
        $this->repository       = app(ActivityRepository::class);
        $this->filterRepository = app(FilterRepository::class);
    }

    /**
    * Get the highlight name
    *
    * @return string
    */
    public function name() : string
    {
        return __('activity.highlights.todays');
    }

    /**
    * Get the highligh count
    *
    * @return integer
    */
    public function count() : int
    {
        return $this->repository->pushCriteria(DueTodayActivitiesCriteria::class)
            ->pushCriteria(OwnActivitiesCriteria::class)
            ->count();
    }

    /**
    * Get the background color class when the highligh count is bigger then zero
    *
    * @return string
    */
    public function bgColorClass() : string
    {
        return 'bg-warning-500';
    }

    /**
    * Get the router to
    *
    * @return array|string
    */
    public function to() : array|string
    {
        $filter = $this->filterRepository->findByFlag('due-today-activities');

        return [
            'name'  => 'activity-index',
            'query' => [
                'filter_id' => $filter?->id,
            ],
        ];
    }
}
