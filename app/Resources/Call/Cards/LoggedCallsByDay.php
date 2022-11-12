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

namespace App\Resources\Call\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Progression;
use App\Criteria\QueriesByUserCriteria;
use App\Contracts\Repositories\CallRepository;

class LoggedCallsByDay extends Progression
{
    /**
     * Calculates logged calls by day
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(CallRepository::class);

        if ($request->has('user_id')) {
            $repository->pushCriteria(new QueriesByUserCriteria($request->user_id));
        }

        return $this->countByDays($request, $repository);
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
        return __('call.cards.by_day');
    }
}
