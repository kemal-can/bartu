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
use App\Criteria\Deal\OwnDealsCriteria;
use App\Criteria\Deal\OpenDealsCriteria;
use App\Contracts\Repositories\DealRepository;
use App\Criteria\Deal\DealsByPipelineCriteria;

class DealsByStage extends DealPresentationCard
{
    /**
     * The default renge/period selected
     *
     * @var integer
     */
    public string|int|null $defaultRange = 30;

    /**
     * Calculate the deals by stage
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(DealRepository::class)
            ->pushCriteria(OwnDealsCriteria::class)
            ->pushCriteria(OpenDealsCriteria::class)
            ->pushCriteria(new DealsByPipelineCriteria($this->getPipeline($request)));

        return $this->withStageLabels(
            $this->byDays('created_at')->count(
                $request,
                $repository,
                'stage_id'
            )
        );
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.by_stage');
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
}
