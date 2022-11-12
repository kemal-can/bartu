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
use App\Criteria\QueriesByUserCriteria;
use App\Contracts\Repositories\DealRepository;

class WonDealsByDay extends Progression
{
    /**
     * Calculates won deals by day
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(DealRepository::class)->pushCriteria(WonDealsCriteria::class);

        if ($user = $this->getUser()) {
            $repository->pushCriteria(new QueriesByUserCriteria($user));
        }

        return $this->countByDays($request, $repository, 'won_date');
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
            60 => __('dates.periods.60_days'),
        ];
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.won_by_date');
    }

    /**
     * Get the user for the card query
     *
     * @return mixed
     */
    protected function getUser()
    {
        if (request()->user()->can('view all deals')) {
            return request()->filled('user_id') ? (int) request()->user_id : null;
        }

        return auth()->user();
    }
}
