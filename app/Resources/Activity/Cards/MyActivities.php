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
use Illuminate\Support\Arr;
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Cards\TableAsyncCard;
use Illuminate\Database\Query\Expression;
use App\Contracts\Repositories\ActivityRepository;
use App\Criteria\Activity\IncompleteActivitiesByUserCriteria;

class MyActivities extends TableAsyncCard
{
    /**
     * Default sort field
     *
     * @var \Illuminate\Database\Query\Expression|string|null
     */
    protected Expression|string|null $sortBy = null;

    /**
     * Provide the repository that will be used to retrieve the items
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    public function repository()
    {
        return resolve(ActivityRepository::class)
            ->with('type')
            ->orderBy(Activity::dueDateQueryExpression())
            ->pushCriteria(IncompleteActivitiesByUserCriteria::class);
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
              ['key' => 'type.name', 'label' => __('activity.type.type'), 'sortable' => false, 'select' => false],
          ];
    }

    /**
     * Get the columns that should be selected in the query
     *
     * @return array
     */
    protected function selectColumns() : array
    {
        return array_merge(
            parent::selectColumns(),
            ['activity_type_id', 'due_time'] // select the due time for full_due_date column
        );
    }

    /**
     * Map the given model into a row
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    protected function mapRow($model)
    {
        $row = parent::mapRow($model);

        // Remove the due time as we are using only to query and create the full_due_date attribute
        Arr::forget($row, 'due_time');

        return array_merge(
            $row,
            [
                'type' => [
                    'swatch_color' => $model->type->swatch_color,
                    'icon'         => $model->type->icon,
                ],
                'is_completed' => $model->isCompleted,
                'due_date'     => $model->due_time ? Carbon::parse($model->full_due_date) : $model->due_date,
                'tdClass'      => $model->isDue ? 'due' : 'not-due',
            ]
        );
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('activity.cards.my_activities');
    }

    /**
     * Get the component name for the card.
     *
     * @return string
     */
    public function component() : string
    {
        return 'my-activities-card';
    }
}
