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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Repositories\ActivityRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\Repositories\SynchronizationRepository;

class OutlookCalendarWebhookController extends Controller
{
    /**
     * Handle the webhook request
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\SynchronizationRepository $repository
     * @param \App\Contracts\Repositories\ActivityRepository $activities
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, SynchronizationRepository $repository, ActivityRepository $activities)
    {
        // https://docs.microsoft.com/en-us/graph/webhooks#notification-endpoint-validation
        if ($request->has('validationToken')) {
            return response($request->validationToken, 200)->header('Content-Type', 'text/plain');
        }

        // https://docs.microsoft.com/en-us/graph/webhooks#change-notification-example
        $synchronizationIds = [];

        foreach ($request->value as $eventChange) {
            try {
                $synchronization = $repository->find($request->value[0]['clientState']);
                // We will remove the deleted event here because there is no option to
                // track the deleted events via the OutlookCalendarSync class
                if (strtolower($eventChange['changeType']) === 'deleted') {
                    $this->handleDeletedEvent(
                        $eventChange['resourceData']['id'],
                        $synchronization->synchronizable->getKey(),
                        $activities
                    );

                    continue;
                }

                $synchronizationIds[] = $synchronization->id;
            } catch (ModelNotFoundException $e) {
            }
        }

        // Not sure if this can happen
        $synchronizationIds = array_unique($synchronizationIds);

        if (count($synchronizationIds) > 0) {
            $repository->withoutOAuthAuthenticationRequired()
                ->findWhereIn('id', $synchronizationIds)
                ->each
                ->ping();
        }

        // https://docs.microsoft.com/en-us/graph/webhooks#processing-the-change-notification
        return response('', 202);
    }

    /**
     * Handle deleted event
     *
     * @param string $eventId
     * @param int $calendarId
     * @param \App\Contracts\Repositories\ActivityRepository $repository
     *
     * @return void
     */
    protected function handleDeletedEvent($eventId, $calendarId, ActivityRepository $repository)
    {
        if ($activity = $repository->findByCalendarSynchronization($eventId, $calendarId)) {
            if ($activity->user && $activity->user->can('delete', $activity)) {
                $repository->deleteWithoutRemovingFromCalendar($activity);
            } else {
                $repository->deleteSynchronization($activity, $eventId, $calendarId);
            }
        }
    }
}
