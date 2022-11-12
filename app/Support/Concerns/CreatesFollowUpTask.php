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

namespace App\Support\Concerns;

use App\Innoclapps\Date\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;
use App\Contracts\Repositories\ActivityRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Contracts\Repositories\ActivityTypeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CreatesFollowUpTask
{
    /**
     * Create follow up task
     *
     * @param string $taskDate
     * @param string $viaResource
     * @param int $viaResourceId
     * @param array $attributes
     *
     * @return \App\Models\Activity
     */
    public function createFollowUpTask($taskDate, $viaResource, $viaResourceId, array $attributes = [])
    {
        $resource     = Innoclapps::resourceByName($viaResource);
        $primaryModel = $resource->repository()->find($viaResourceId);
        $repository   = resolve(ActivityRepository::class);

        $dateInUTCTimezone = Carbon::asCurrentTimezone($taskDate)
            ->setHour(config('app.defaults.hour'))
            ->setMinute(config('app.defaults.minutes'))
            ->setSecond(0)
            ->inAppTimezone();

        $task = $repository->create(array_merge([
            'activity_type_id'        => resolve(ActivityTypeRepository::class)->findByFlag('task')->id,
            'title'                   => __('activity.follow_up_with_title', ['with' => $primaryModel->display_name]),
            'due_date'                => $dueDate = $dateInUTCTimezone->format('Y-m-d'),
            'due_time'                => $dateInUTCTimezone->format('H:i:s'),
            'end_date'                => $dueDate,
            'reminder_minutes_before' => config('app.defaults.reminder_minutes'),
            'user_id'                 => Auth::id(),
        ], $attributes));

        $repository->sync($task->id, $resource->associateableName(), [$viaResourceId]);
        $task = $task->resource()->displayQuery()->find($task->id);

        try {
            resolve(ResourceRequest::class)->resource()
                ->jsonResource()::createdFollowUpTask($task);
        } catch (NotFoundHttpException $e) {
            // When using the trait with non registered resource e.q. message
        }

        return $task;
    }

    /**
     * Check whether a follow up task should be created
     * It checks via the request data attributes
     *
     * @param array $data
     *
     * @return boolean
     */
    public function shouldCreateFollowUpTask(array $data) : bool
    {
        return isset($data['task_date']) && ! empty($data['task_date']);
    }
}
