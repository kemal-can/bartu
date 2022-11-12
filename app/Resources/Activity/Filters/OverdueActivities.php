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

use App\Innoclapps\Filters\Radio;

class OverdueActivities extends Radio
{
    /**
     * Create new instance of OverdueActivities class
     */
    public function __construct()
    {
        parent::__construct('overdue', __('activity.overdue'));

        $this->options(['yes' => __('app.yes'), 'no' => __('app.no')])
            ->query(function ($builder, $value, $condition) {
                return $builder->where(fn ($builder) => $builder->overdue($value === 'yes' ? '<=' : '>'), null, null, $condition);
            });
    }
}
