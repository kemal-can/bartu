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

namespace App\Resources\Contact\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Progression;
use App\Criteria\Contact\OwnContactsCriteria;
use App\Contracts\Repositories\ContactRepository;

class ContactsByDay extends Progression
{
    /**
     * Calculates contacts created by day
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByDays($request, resolve(ContactRepository::class)
            ->pushCriteria(OwnContactsCriteria::class));
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
        return __('contact.cards.by_day');
    }
}
