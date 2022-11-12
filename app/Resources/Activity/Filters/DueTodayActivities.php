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

namespace App\Resources\Activity\Filters;

use App\Innoclapps\Filters\Filter;
use App\Criteria\Activity\DueTodayActivitiesCriteria;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;

class DueTodayActivities extends Filter
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * Initialize DueTodayActivities class
     */
    public function __construct()
    {
        parent::__construct('due_today', __('activity.filters.due_today'));

        $this->asStatic()->query(function ($builder, $value, $condition) {
            return $builder->where(function ($builder) {
                return DueTodayActivitiesCriteria::applyQuery($builder);
            }, null, null, $condition);
        });
    }
}
