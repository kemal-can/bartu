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

namespace App\Resources\Company\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Progression;
use App\Criteria\Company\OwnCompaniesCriteria;
use App\Contracts\Repositories\CompanyRepository;

class CompaniesByDay extends Progression
{
    /**
     * Calculates companies created by day
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByDays($request, resolve(CompanyRepository::class)
            ->pushCriteria(OwnCompaniesCriteria::class));
    }

    /**
     * Get the ranges available for the chart.
     *
     * @return array
     */
    public function ranges() : array
    {
        return [
            7  => __('dates.periods.7_days'),
            15 => __('dates.periods.15_days'),
            30 => __('dates.periods.30_days'),
        ];
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('company.cards.by_day');
    }
}
