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

use App\Enums\DealStatus;
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Cards\TableCard;
use App\Innoclapps\Facades\Innoclapps;
use App\Contracts\Repositories\UserRepository;

class CreatedDealsBySaleAgent extends TableCard
{
    /**
     * Provide the table items
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function items() : iterable
    {
        $range        = $this->getCurrentRange(request());
        $startingDate = $this->getStartingDate($range, static::BY_MONTHS);
        $endingDate   = Carbon::asAppTimezone();

        return resolve(UserRepository::class)
            ->withCount(['createdDeals' => function ($query) use ($startingDate, $endingDate) {
                return $query->whereBetween('created_at', [$startingDate, $endingDate]);
            }])
            ->all()
            ->map(function ($user) use ($startingDate, $endingDate) {
                return [
                    'name'                => $user->name,
                    'created_deals_count' => $user->created_deals_count,

                    'forecast_amount' => money(
                        $user->createdDeals()->whereBetween('created_at', [$startingDate, $endingDate])->sum('amount'),
                        Innoclapps::currency(),
                        true
                    )->format(),

                    'closed_amount' => money(
                        $user->createdDeals()->where('status', DealStatus::won)
                            ->whereBetween('created_at', [$startingDate, $endingDate])
                            ->sum('amount'),
                        Innoclapps::currency(),
                        true
                    )->format(),
                ];
            })->sortByDesc('created_deals_count')->values();
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
              ['key' => 'name', 'label' => __('user.sales_agent')],
              ['key' => 'created_deals_count', 'label' => __('deal.total_created')],
              ['key' => 'forecast_amount', 'label' => __('deal.forecast_amount')],
              ['key' => 'closed_amount', 'label' => __('deal.closed_amount')],
          ];
    }

    /**
     * Card title
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.created_by_sale_agent');
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
    * jsonSerialize
    *
    * @return array
    */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'help' => __('deal.cards.created_by_sale_agent_info'),
        ]);
    }
}
