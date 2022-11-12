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

class OpenActivities extends Filter
{
    /**
     * Initialize OpenActivities Class
     */
    public function __construct()
    {
        parent::__construct('open_activities', __('activity.filters.open'));

        $this->asStatic()->query(function ($builder, $value, $condition) {
            return $builder->where(fn ($builder) => $builder->incomplete(), null, null, $condition);
        });
    }
}
