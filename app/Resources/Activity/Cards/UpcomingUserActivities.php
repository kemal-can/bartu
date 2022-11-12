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

namespace App\Resources\Activity\Cards;

use App\Models\Activity;
use App\Innoclapps\Cards\Card;
use App\Innoclapps\Cards\TableAsyncCard;
use Illuminate\Database\Query\Expression;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\UpcomingActivitiesCriteria;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;
use App\Criteria\Activity\IncompleteActivitiesByUserCriteria;

class UpcomingUserActivities extends TableAsyncCard
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * The default selected range
     *
     * @var string
     */
    public string|int|null $defaultRange = 'this_month';

    /**
     * Provide the repository that will be used to retrieve the items
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    public function repository()
    {
        return resolve(ActivityRepository::class)
            ->pushCriteria(IncompleteActivitiesByUserCriteria::class)
            ->pushCriteria(UpcomingActivitiesCriteria::class)
            ->scopeQuery(function ($query) {
                return $query->whereBetween(Activity::dueDateQueryExpression(), $this->getBetweenArguments(
                    $this->getCurrentRange(request())
                ));
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
              ['key' => 'title', 'label' => __('activity.title'), 'sortable' => true],
              ['key' => 'due_date', 'label' => __('activity.due_date'), 'sortable' => true],
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
     * Get the sort column
     *
     * @return \Illuminate\Database\Query\Expression
     */
    protected function getSortColumn() : Expression
    {
        return Activity::dueDateQueryExpression();
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('activity.cards.upcoming');
    }
}
