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

namespace App\Calendar;

use App\Models\Activity;
use App\Models\Calendar;
use Google_Service_Exception;
use Illuminate\Support\Carbon;
use App\Models\Synchronization;
use App\Contracts\Synchronizable;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\URL;
use App\Events\CalendarSyncFinished;
use Google_Service_Calendar_Channel;
use App\Contracts\SynchronizesViaWebhook;
use App\Innoclapps\Facades\Google as Client;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\CalendarRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\Repositories\SynchronizationRepository;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class GoogleCalendarSync extends CalendarSynchronization implements Synchronizable, SynchronizesViaWebhook
{
    /**
     * @var \App\Contracts\Repositories\SynchronizationRepository
     */
    protected SynchronizationRepository $synchronizations;

    /**
     * @var \App\Innoclapps\Contracts\Repositories\OAuthAccountRepository
     */
    protected OAuthAccountRepository $accounts;

    /**
     * @var \App\Contracts\Repositories\ActivityRepository
     */
    protected ActivityRepository $activities;

    /**
     * Cancelled status name
     */
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @var string
     */
    protected string $webHookUrl;

    /**
     * Initialize new OutlookCalendarSync class
     *
     * @param \App\Models\Calendar $calendar
     */
    public function __construct(protected Calendar $calendar)
    {
        $this->synchronizations = resolve(SynchronizationRepository::class);
        $this->accounts         = resolve(OAuthAccountRepository::class);
        $this->activities       = resolve(ActivityRepository::class);
        $this->webHookUrl       = URL::asAppUrl('webhook/google');
    }

    /**
     * Synchronize the data for the given synchronization
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function synchronize(Synchronization $synchronization)
    {
        $pageToken        = null;
        $syncToken        = $synchronization->token;
        $changesPerformed = false;

        Client::connectUsing($this->calendar->email);

        $service = Client::calendar();

        do {
            $tokens = compact('pageToken', 'syncToken');

            try {
                $list = $service->events->listEvents(
                    $this->calendar->calendar_id,
                    $this->listQueryString($tokens, $syncToken)
                );

                // We will store the default reminders when the list of events is retrieved
                // Google provides the default reminders per calendar in the listEvents request
                // see https://gsuite-developers.googleblog.com/2012/01/calendar-api-v3-best-practices.html
                // The defaults for the given calendar are included at the top of any event listing result. This way, reminder settings for all events in the result can be determined by the client without having to make the additional API call to the corresponding entry in the Calendar List collection.
                $this->storeDefaultReminders($list);
            } catch (Google_Service_Exception $e) {
                if ($e->getCode() === 410) {
                    $this->synchronizations->update(['token' => null], $synchronization->getKey());

                    $this->synchronize($synchronization);

                    return;
                } elseif ($e->getCode() === 401) {
                    $this->setAccountRequiresAuth();

                    return;
                }

                throw $e;
            }

            if ($this->processChangedEvents($list->getItems())) {
                $changesPerformed = true;
            }

            $pageToken = $list->getNextPageToken();
        } while ($pageToken);

        $this->synchronizations->update([
            'token'                => $list->getNextSyncToken(),
            'last_synchronized_at' => now(),
        ], $synchronization->getKey());

        event(new CalendarSyncFinished($synchronization->synchronizable, $changesPerformed));
    }

    /**
     * Iterage over the changed events
     *
     * @param array $events
     *
     * @return bool
     */
    protected function processChangedEvents($events)
    {
        $changesPerformed = false;

        foreach ($events as $event) {
            if (strtolower($event->getStatus()) === static::STATUS_CANCELLED) {
                $this->handleDeletedEvent($event);

                continue;
            }

            // At this time syncing recurring events is not supported.
            if ($this->isRecurring($event)) {
                continue;
            }

            list($model, $guestsUpdated) = $this->saveChangedEvent($event);

            if ($model->wasRecentlyCreated || $model->wasChanged() || $guestsUpdated) {
                $changesPerformed = true;
            }
        }

        return $changesPerformed;
    }

    /**
     * Save the changed event
     *
     * @param \Google_Calendar_Event $event
     *
     * @return array
     */
    protected function saveChangedEvent($event)
    {
        $dueDate  = $this->parseDatetime($event->getStart());
        $endDate  = $this->parseDatetime($event->getEnd());
        $isAllDay = $this->isAllDay($event);

        $activity = [
            'title'                   => $event->getSummary() ?: '(No Title)',
            'description'             => $event->getDescription(),
            'due_date'                => $dueDate->format('Y-m-d'),
            'due_time'                => ! $isAllDay ? $dueDate->format('H:i') . ':00' : null,
            'end_date'                => $endDate->format('Y-m-d'),
            'end_time'                => ! $isAllDay ? $endDate->format('H:i') . ':00' : null,
            'reminder_minutes_before' => $this->determineReminderMinutesBefore($event),
            'guests'                  => collect($event->getAttendees())->map(function ($attendee) {
                return [
                    'email' => $attendee->getEmail(),
                    'name'  => $attendee->getDisplayName(),
                ];
            })->all(),
        ];

        return $this->processViaChange(
            $activity,
            $this->calendar->activity_type_id,
            $this->calendar->user,
            $event->getId(),
            $this->calendar->getKey()
        );
    }

    /**
     * Determine reminder minutes before for the given event
     *
     * @param \Google_Calendar_Event $event
     *
     * @return int|null
     */
    protected function determineReminderMinutesBefore($event)
    {
        // There are no reminders on Holidays calendars
        if (is_null($event->getReminders())) {
            return null;
        }

        // First, we will check if the event is actually using the default
        // calendar reminders, if yes, we will take the default calendar reminders
        // from the calendar data attribute
        // otherwise, we will just use the event configured reminders

        // From the configured reminders, we will retrieve the min reminder
        // minutes to use in bartu as reminder_minutes_before

        if ($event->getReminders()->getUseDefault()) {
            $defaults = array_values($this->calendar->data['defaultReminders']);

            if (count($defaults) > 0) {
                return min($defaults);
            }
        }

        return collect($event->getReminders())->mapWithKeys(function ($reminder) {
            return [$reminder->getMethod() => $reminder->getMinutes()];
        })->values()->min();
    }

    /**
     * Remove event
     *
     * @param \Google_Calendar_Event $event
     *
     * @return void
     */
    protected function handleDeletedEvent($event)
    {
        if ($activity = $this->activities->findByCalendarSynchronization($event->getId(), $this->calendar)) {
            if ($activity->user && $activity->user->can('delete', $activity)) {
                $this->activities->deleteWithoutRemovingFromCalendar($activity);
            } else {
                $this->activities->deleteSynchronization(
                    $activity,
                    $event->getId(),
                    $this->calendar->getKey()
                );
            }
        }
    }

    /**
     * Update event in the calendar from the given activity
     *
     * @param \App\Models\Activity $activity
     * @param string $eventId
     *
     * @return void
     */
    public function updateEvent(Activity $activity, $eventId)
    {
        $this->handleRequestExceptions(function () use ($activity, $eventId) {
            Client::calendar()->events->update(
                $this->calendar->calendar_id,
                $eventId,
                $this->eventPayload($activity)
            );
        });
    }

    /**
     * Create event in the calendar from the given activity
     *
     * @param \App\Models\Activity $activity
     *
     * @return void
     */
    public function createEvent(Activity $activity)
    {
        $this->handleRequestExceptions(function () use ($activity) {
            $event = Client::calendar()->events->insert(
                $this->calendar->calendar_id,
                $this->eventPayload($activity)
            );

            $this->activities->addSynchronization(
                $activity,
                $event->getId(),
                $this->calendar->getKey()
            );
        });
    }

    /**
     * Update event from the calendar for the given activity
     *
     * @param int $activityId
     * @param string $eventId
     *
     * @return void
     */
    public function deleteEvent($activityId, $eventId)
    {
        $this->handleRequestExceptions(function () use ($eventId, $activityId) {
            Client::calendar()->events->delete($this->calendar->calendar_id, $eventId);

            // We will check if the ModelNotFoundException is throw
            // It may happen if the deleteEvent is queued with closure via the
            // repository delete method the activity to be actuall deleted,
            // in this case, we won't need to clear the synchronizations as they are already cleared
            try {
                $activity = $this->activities->find($activityId);

                $this->activities->deleteSynchronization(
                    $activity,
                    $eventId,
                    $this->calendar->getKey()
                );
            } catch (ModelNotFoundException $e) {
            }
        });
    }

    /**
     * Create event payload for create/update from the given activity
     *
     * @param \App\Models\Activity $activity
     *
     * @return \Google_Service_Calendar_Event
     */
    protected function eventPayload($activity)
    {
        return new Google_Service_Calendar_Event(
            (new GoogleEventPayload($activity, $this->calendar))->toArray()
        );
    }

    /**
     * Check whether the given Google event is recurring event
     *
     * @param \Google_Calendar_Event $event
     *
     * @return boolean
     */
    protected function isRecurring($event)
    {
        return ! is_null($event->getRecurrence());
    }

    /**
     * Check whether the given Google event is all day event
     *
     * @param \Google_Calendar_event $event
     *
     * @return boolean
     */
    protected function isAllDay($event)
    {
        return ! is_null($event->getStart()->date);
    }

    /**
     * Get the list event endpoint query string
     *
     * @param array $tokens
     * @param string|null $syncToken
     *
     * @return array
     */
    protected function listQueryString($tokens, $syncToken)
    {
        // Can't use params and $syncToken at the same time, as the params
        // are remembered in the $syncToken, we don't need them, only on the initial sync
        return array_merge($tokens, ! $syncToken ? [
            'showDeleted'  => true,
            'timeMin'      => $this->calendar->startSyncFrom()->format(\DateTimeInterface::RFC3339),
            'singleEvents' => false, // exclude the recurrence child
        ] : []);
    }

    /**
     * Parse the given date
     *
     * @param object $googleDatetime
     *
     * @return \Carbon\Carbon
     */
    protected function parseDatetime($googleDatetime)
    {
        $rawDatetime = $googleDatetime->dateTime ?: $googleDatetime->date;

        return Carbon::parse($rawDatetime)->timezone(config('app.timezone'));
    }

    /**
     * Set account requires authentication to the calendar OAuth account
     */
    protected function setAccountRequiresAuth()
    {
        $this->accounts->setRequiresAuthentication($this->calendar->access_token_id);
    }

    /**
     * Store the calendar default reminders
     *
     * @param mixed $list
     *
     * @return void
     */
    protected function storeDefaultReminders($list)
    {
        $currentReminders = $this->calendar->data['defaultReminders'] ?? null;

        $defaultReminders = collect($list->getDefaultReminders())->mapWithKeys(function ($reminder) {
            return [$reminder->getMethod() => $reminder->getMinutes()];
        })->all();

        // We will check if the reminders are not yet set or they are actually changed
        // from the last time they were saved to save one query each minute
        if (is_null($currentReminders) || count(array_diff($defaultReminders, $currentReminders)) > 0) {
            resolve(CalendarRepository::class)->unguarded(function ($repository) use ($defaultReminders) {
                $updated = $repository->update([
                    'data' => array_merge($this->calendar->data ?? [], ['defaultReminders' => $defaultReminders]),
                ], $this->calendar->getKey());
                $this->calendar->data = $updated->data;
            });
        }
    }

    /**
     * Make a request and catch common exceptions
     *
     * @param \Closure $callback
     *
     * @return void
     */
    protected function handleRequestExceptions(\Closure $callback)
    {
        Client::connectUsing($this->calendar->email);

        try {
            $callback();
        } catch (Google_Service_Exception $e) {
            if ($e->getCode() === 401) {
                $this->setAccountRequiresAuth();

                return;
            } elseif ($e->getCode() === 404) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Subscribe for changes for the given synchronization
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function watch(Synchronization $synchronization)
    {
        try {
            $response = Client::connectUsing($this->calendar->email)
                ->calendar()
                ->events->watch(
                    $this->calendar->calendar_id,
                    $this->createSubscriptionInstance($synchronization)
                );

            $this->synchronizations->markAsWebhookSynchronizable(
                $response->getResourceId(),
                Carbon::createFromTimestampMs($response->getExpiration()),
                $synchronization
            );
        } catch (\Google_Service_Exception $e) {
            // If we reach an error at this point, it is likely that
            // push notifications are not allowed for this resource
            // or the application URL is not accessible or with invalid SSL certificate.
            // Instead we will sync it manually at regular interval.
        }
    }

    /**
     * Unsubscribe from changes for the given synchronization
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function unwatch(Synchronization $synchronization)
    {
        // If resource_id is null then the synchronization
        // does not have an associated Google Channel and
        // therefore there is nothing to stop at this point.
        if (! $this->calendar->synchronization->isSynchronizingViaWebhook()) {
            return;
        }

        try {
            Client::connectUsing($this->calendar->email)
                ->calendar()
                ->channels
                ->stop(
                    $this->createSubscriptionInstance($synchronization)
                );

            $this->synchronizations->unmarkAsWebhookSynchronizable($synchronization);
        } catch (\Google_Service_Exception $e) {
        }
    }

    /**
     * Create new Google Channel subscription instance
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return \Google_Service_Calendar_Channel
     */
    protected function createSubscriptionInstance($synchronization)
    {
        $channel = new Google_Service_Calendar_Channel;
        $channel->setId($synchronization->id);
        $channel->setResourceId($synchronization->resource_id);
        $channel->setType('web_hook');
        $channel->setAddress($this->webHookUrl);

        return $channel;
    }
}
