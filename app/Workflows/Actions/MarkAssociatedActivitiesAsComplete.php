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

namespace App\Workflows\Actions;

use App\Innoclapps\Workflow\Action;
use App\Contracts\Repositories\ActivityRepository;

class MarkAssociatedActivitiesAsComplete extends Action
{
    /**
     * @var \App\Contracts\Repositories\ActivityRepository
     */
    protected ActivityRepository $repository;

    /**
     * Initialize MarkAssociatedActivitiesAsComplete
     */
    public function __construct()
    {
        $this->repository = resolve(ActivityRepository::class);
    }

    /**
    * Action name
    *
    * @return string
    */
    public static function name() : string
    {
        return __('deal.workflows.actions.mark_associated_activities_as_complete');
    }

    /**
     * Run the trigger
     *
     * @return \App\Models\Activity
     */
    public function run()
    {
        $this->model->incompleteActivities->each(function ($activity) {
            $this->repository->complete($activity);
        });
    }

    /**
    * Action available fields
    *
    * @return array
    */
    public function fields() : array
    {
        return [];
    }
}
