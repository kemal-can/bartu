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

namespace App\Resources\Deal\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Progression;
use App\Criteria\Deal\WonDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class WonDealsRevenueByMonth extends Progression
{
    /**
     * Indicates whether the chart values are amount
     *
     * @var boolean
     */
    protected bool $amountValue = true;

    /**
     * Calculate the won deals revenue by month
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(DealRepository::class)->pushCriteria(WonDealsCriteria::class);

        return $this->sumByMonths($request, $repository, 'amount', 'won_date');
    }

    /**
     * Get the ranges available for the chart.
     *
     * @return array
     */
    public function ranges() : array
    {
        return [
             3  => __('dates.periods.last_3_months'),
             6  => __('dates.periods.last_6_months'),
             12 => __('dates.periods.last_12_months'),
        ];
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.won_by_revenue_by_month');
    }
}
