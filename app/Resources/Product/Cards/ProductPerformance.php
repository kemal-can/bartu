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

namespace App\Resources\Product\Cards;

use App\Innoclapps\Facades\Innoclapps;
use App\Criteria\Deal\OwnDealsCriteria;
use App\Criteria\Deal\WonDealsCriteria;
use App\Innoclapps\Cards\TableAsyncCard;
use Illuminate\Database\Query\Expression;
use App\Contracts\Repositories\ProductRepository;

class ProductPerformance extends TableAsyncCard
{
    /**
     * Default sort field
     *
     * @var \Illuminate\Database\Query\Expression|string|null
     */
    protected Expression|string|null $sortBy = 'sold_count';

    /**
     * Default sort direction
     *
     * @var string
     */
    protected string $sortDirection = 'desc';

    /**
     * Provide the repository that will be used to retrieve the items
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    public function repository()
    {
        $interestInProductCallback = function ($query) {
            return $query->whereHas('billable', function ($query) {
                return $query->whereHas('billableable', function ($query) {
                    return OwnDealsCriteria::applyQuery($query);
                });
            });
        };

        return resolve(ProductRepository::class)->withCount([
            'billables as sold_count'     => $this->ownAndWonDealsQueryCallback(),
            'billables as interest_count' => $interestInProductCallback,
        ])
            ->withSum([
            'billables as sold_sum_amount' => $this->ownAndWonDealsQueryCallback(),
        ], 'amount');
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
            [
                'key'      => 'name',
                'label'    => __('product.name'),
                'sortable' => true,
            ],
            [
                'key'      => 'interest_count',
                'label'    => __('product.interest_in_product'),
                'sortable' => true,
            ],
            [
                'key'      => 'sold_count',
                'label'    => __('product.total_sold'),
                'sortable' => true,
            ],
            [
                'key'      => 'sold_sum_amount',
                'label'    => __('product.sold_amount_exc_tax'),
                'sortable' => true,
                'format'   => function ($model) {
                    return money($model->sold_amount ?: 0, Innoclapps::currency(), true)->format();
                },
            ],
        ];
    }

    /**
     * Get the own and won deals query callback
     *
     * @return callable
     */
    protected function ownAndWonDealsQueryCallback()
    {
        return function ($query) {
            return $query->whereHas('billable', function ($query) {
                return $query->whereHas('billableable', function ($query) {
                    $query = OwnDealsCriteria::applyQuery($query);

                    return WonDealsCriteria::applyQuery($query);
                });
            });
        };
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('product.cards.performance');
    }

    /**
    * jsonSerialize
    *
    * @return array
    */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'help' => __('product.cards.performance_info'),
        ]);
    }
}
