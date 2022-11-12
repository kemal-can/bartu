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

use App\Innoclapps\Cards\Card;
use App\Criteria\QueriesByUserCriteria;
use App\Criteria\Deal\OpenDealsCriteria;
use App\Innoclapps\Cards\TableAsyncCard;
use Illuminate\Database\Query\Expression;
use App\Contracts\Repositories\DealRepository;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;

class ClosingDeals extends TableAsyncCard
{
    use ProvidesBetweenArgumentsViaString;

    /**
    * The default renge/period selected
    *
    * @var string
    */
    public string|int|null $defaultRange = 'this_month';

    /**
     * Default sort field
     *
     * @var \Illuminate\Database\Query\Expression|string|null
     */
    protected Expression|string|null $sortBy = 'expected_close_date';

    /**
     * Provide the repository that will be used to retrieve the items
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    public function repository()
    {
        $repository = resolve(DealRepository::class);

        if ($user = $this->getUser()) {
            $repository->pushCriteria(new QueriesByUserCriteria($user));
        }

        return $repository->pushCriteria(OpenDealsCriteria::class)
            ->scopeQuery(function ($query) {
                $period = $this->getBetweenArguments(request()->range ?? $this->defaultRange);

                return $query->whereNotNull('expected_close_date')->whereBetween('expected_close_date', $period);
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
              ['key' => 'name', 'label' => __('fields.deals.name'), 'sortable' => true],
              ['key' => 'expected_close_date', 'label' => __('fields.deals.expected_close_date'), 'sortable' => true],
          ];
    }

    /**
    * Get the ranges available for the chart.
    *
    * @return array
    */
    public function ranges() : array
    {
        return [
            'this_week'  => __('dates.this_week'),
            'this_month' => __('dates.this_month'),
            'next_week'  => __('dates.next_week'),
            'next_month' => __('dates.next_month'),
        ];
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('deal.cards.closing');
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
