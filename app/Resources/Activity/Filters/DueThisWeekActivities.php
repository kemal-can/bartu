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

use App\Models\Activity;
use App\Innoclapps\Filters\Filter;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;

class DueThisWeekActivities extends Filter
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * Initialize DueThisWeekActivities class
     */
    public function __construct()
    {
        parent::__construct('due_this_week', __('activity.filters.due_this_week'));

        $this->asStatic()->query(function ($builder, $value, $condition) {
            return $builder->where(function ($builder) {
                return $builder->whereBetween(
                    Activity::dueDateQueryExpression(),
                    $this->getBetweenArguments('this_week')
                )->incomplete();
            }, null, null, $condition);
        });
    }
}
