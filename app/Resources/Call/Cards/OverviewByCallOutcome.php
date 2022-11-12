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
use App\Criteria\QueriesByUserCriteria;
use App\Innoclapps\Charts\Presentation;
use App\Innoclapps\Criteria\RelatedCriteria;
use App\Contracts\Repositories\CallRepository;
use App\Contracts\Repositories\CallOutcomeRepository;

class OverviewByCallOutcome extends Presentation
{
    /**
    * The default renge/period selected
    *
    * @var integer
    */
    public string|int|null $defaultRange = 30;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $outcomes;

    /**
     * Calculated overview by call outcome
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(CallRepository::class);

        if ($request->user()->isSuperAdmin() && $request->has('user_id')) {
            $repository->pushCriteria(new QueriesByUserCriteria($request->user_id));
        } else {
            $repository->pushCriteria(RelatedCriteria::class);
        }

        return $this->byDays('date')
            ->count($request, $repository, 'call_outcome_id')
            ->label(function ($value) {
                return $this->outcomes()->find($value)->name;
            });
    }

    /**
    * Get the ranges available for the chart.
    *
    * @return array
    */
    public function ranges() : array
    {
        return [
            7   => __('dates.periods.7_days'),
            15  => __('dates.periods.15_days'),
            30  => __('dates.periods.30_days'),
            60  => __('dates.periods.60_days'),
            90  => __('dates.periods.90_days'),
            365 => __('dates.periods.365_days'),
        ];
    }

    /**
     * Get all available outcomes
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function outcomes()
    {
        if (! $this->outcomes) {
            $this->outcomes = resolve(CallOutcomeRepository::class)->columns(['id', 'name'])->all();
        }

        return $this->outcomes;
    }

    /**
    * The card name
    *
    * @return string
    */
    public function name() : string
    {
        return __('call.cards.outcome_overview');
    }
}
