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
use App\Criteria\Deal\WonDealsCriteria;
use App\Contracts\Repositories\DealRepository;
use App\Criteria\Deal\DealsByPipelineCriteria;

class DealsWonInStage extends DealPresentationCard
{
    /**
     * Axis Y Offset
     *
     * @var integer
     */
    public int $axisYOffset = 150;

    /**
    * Indicates whether the cart is horizontal
    *
    * @var boolean
    */
    public bool $horizontal = true;

    /**
    * The default renge/period selected
    *
    * @var string
    */
    public string|int|null $defaultRange = 3;

    /**
     * Calculate the deals lost in stage
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(DealRepository::class)
            ->pushCriteria(OwnDealsCriteria::class)
            ->pushCriteria(new DealsByPipelineCriteria($this->getPipeline($request)))
            ->pushCriteria(WonDealsCriteria::class);

        return $this->withStageLabels(
            $this->byMonths('won_date')->count($request, $repository, 'stage_id')
        );
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
        return __('deal.cards.won_in_stage');
    }

    /**
    * jsonSerialize
    *
    * @return array
    */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'help' => __('deal.cards.won_in_stage_info'),
        ]);
    }
}
