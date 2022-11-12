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

use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Cards\TableCard;
use App\Criteria\Deal\OwnDealsCriteria;
use App\Contracts\Repositories\DealRepository;

class RecentlyCreatedDeals extends TableCard
{
    /**
     * Limit the number of records shown in the table
     *
     * @var integer
     */
    protected $limit = 20;

    /**
     * Created in the last 30 days
     *
     * @var integer
     */
    protected $days = 30;

    /**
     * Provide the table items
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function items() : iterable
    {
        return resolve(DealRepository::class)
            ->pushCriteria(OwnDealsCriteria::class)
            ->with('stage')
            ->columns(['id', 'name', 'created_at', 'stage_id'])
            ->orderBy('created_at', 'desc')
            ->limit($this->limit)
            ->findWhere(
                [['created_at', '>', Carbon::asCurrentTimezone()->subDays($this->days)->inAppTimezone()]]
            )->map(function ($deal) {
                return [
                    'id'         => $deal->id,
                    'name'       => $deal->name,
                    'stage'      => $deal->stage,
                    'created_at' => $deal->created_at,
                    'path'       => $deal->path,
                ];
            });
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
              ['key' => 'name', 'label' => __('fields.deals.name')],
              ['key' => 'stage.name', 'label' => __('fields.deals.stage.name')],
              ['key' => 'created_at', 'label' => __('app.created_at')],
          ];
    }

    /**
     * Card title
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.recently_created');
    }

    /**
    * jsonSerialize
    *
    * @return array
    */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'help' => __('deal.cards.recently_created_info', ['total' => $this->limit, 'days' => $this->days]),
        ]);
    }
}
