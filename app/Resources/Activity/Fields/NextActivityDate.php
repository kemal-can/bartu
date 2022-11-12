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

namespace App\Resources\Activity\Fields;

use App\Models\Activity;
use App\Innoclapps\Facades\Format;
use App\Innoclapps\Fields\BelongsTo;
use App\Contracts\Repositories\ActivityRepository;

class NextActivityDate extends BelongsTo
{
    /**
    * Initialize new NextActivityDate instance
    */
    public function __construct()
    {
        parent::__construct(
            'nextActivity',
            $this->repository(),
            __('fields.next_activity_date'),
            'next_activity_date'
        );

        $this->exceptOnForms()
            ->excludeFromImport()
            ->help(__('fields.next_activity_date_info'))
            ->hidden();

        $this->resolveUsing(function ($model) {
            if ($model->relationLoaded('nextActivity')) {
                return $model->nextActivity?->full_due_date;
            }
        });

        $this->displayUsing(function ($model) {
            if ($model->relationLoaded('nextActivity') && $model->nextActivity) {
                return Format::separateDateAndTime(
                    $model->nextActivity->due_date,
                    $model->nextActivity->due_time
                );
            }
        });

        $this->tapIndexColumn(function ($column) {
            $column->orderByColumn(function ($data) {
                return Activity::dueDateQueryExpression();
            })
                ->select(['due_time'])
                ->queryAs(Activity::dueDateQueryExpression('next_activity_date'))
                ->displayAs(function ($model) {
                    if ($model->nextActivity) {
                        $date = $model->nextActivity->next_activity_date;
                        $method = $model->nextActivity->due_time ? 'dateTime' : 'date';

                        return Format::$method($date);
                    }

                    return '--';
                });
        });
    }

    /**
     * Get the activity repository
     *
     * @return BaseRepository
     */
    protected function repository()
    {
        return resolve(ActivityRepository::class);
    }
}
