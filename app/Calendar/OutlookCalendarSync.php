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
use Illuminate\Support\Carbon;
use App\Models\Synchronization;
use App\Contracts\Synchronizable;
use Illuminate\Support\Facades\URL;
use App\Events\CalendarSyncFinished;
use Microsoft\Graph\Model\Subscription;
use App\Contracts\SynchronizesViaWebhook;
use GuzzleHttp\Exception\ClientException;
use App\Innoclapps\Facades\Microsoft as Api;
use Microsoft\Graph\Model\Event as EventModel;
use App\Contracts\Repositories\ActivityRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\Repositories\SynchronizationRepository;
use App\Calendar\Exceptions\InvalidNotificationURLException;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OutlookCalendarSync extends CalendarSynchronization implements Synchronizable, SynchronizesViaWebhook
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
        $this->webHookUrl       = URL::asAppUrl('webhook/outlook-calendar');
        $this->synchronizations = resolve(SynchronizationRepository::class);
        $this->accounts         = resolve(OAuthAccountRepository::class);
        $this->activities       = resolve(ActivityRepository::class);
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
        Api::connectUsing($this->calendar->email);

        try {
            $iterator = Api::createCollectionGetRequest($this->createEndpoint())
                ->setReturnType(EventModel::class);

            $events = Api::iterateCollectionRequest($iterator);
        } catch (IdentityProviderException $e) {
            $this->accounts->setRequiresAuthentication($this->calendar->access_token_id);

            return;
        }

        $changesPerformed = $this->processChangedEvents($events ?? []);

        $this->synchronizations->update([
            'last_synchronized_at' => now(),
        ], $synchronization->getKey());

        event(new CalendarSyncFinished($synchronization->synchronizable, $changesPerformed));
    }

    /**
     * Iterage over the changed events
     *
     * @param array $events
     *
     * @return boolean
     */
    protected function processChangedEvents($events) : bool
    {
        $changesPerformed = false;

        foreach ($events as $event) {
            $dueDate  = Carbon::parse($event->getStart()->getDateTime());
            $endDate  = Carbon::parse($event->getEnd()->getDateTime());
            $isAllDay = $event->getIsAllDay();

            $activity = [
                'title'                   => $event->getSubject() ?? '(No Title)',
                'description'             => $event->getBody()->getContent(),
                'due_date'                => $dueDate->format('Y-m-d'),
                'due_time'                => ! $isAllDay ? $dueDate->format('H:i') . ':00' : null,
                'end_date'                => ($isAllDay ? $endDate->sub(1, 'second') : $endDate)->format('Y-m-d'),
                'end_time'                => ! $isAllDay ? $endDate->format('H:i') . ':00' : null,
                'reminder_minutes_before' => $event->getReminderMinutesBeforeStart(),
                'guests'                  => collect($event->getAttendees())->map(function ($attendee) {
                    return [
                        'email' => $attendee['emailAddress']['address'],
                        'name'  => $attendee['emailAddress']['name'],
                    ];
                })->all(),
            ];

            list($model, $guestsUpdated) = $this->processViaChange(
                $activity,
                $this->calendar->activity_type_id,
                $this->calendar->user,
                $event->getId(),
                $this->calendar->getKey()
            );

            if ($model->wasRecentlyCreated || $model->wasChanged() || $guestsUpdated) {
                $changesPerformed = true;
            }
        }

        return $changesPerformed;
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
        $this->handleRequestExceptions(function () use ($synchronization) {
            try {
                $response = Api::createPostRequest(
                    '/subscriptions',
                    $this->createSubscriptionInstance($synchronization)->getProperties()
                )
                    ->execute()
                    ->getBody();

                $this->synchronizations->markAsWebhookSynchronizable(
                    $response['id'],
                    $response['expirationDateTime'],
                    $synchronization
                );
            } catch (ClientException $e) {
                // We will throw an exceptions for invalid URL and won't allow the
                // user to sync without valid URL as the Outlook synchronization works only
                // with webhooks and cannot use the polling method as we cannot detect deleted events when polling
                if ($this->isInvalidExceptionUrlMessage($e->getMessage())) {
                    throw new InvalidNotificationURLException;
                }

                throw $e;
            }
        });
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
        // perhaps subscription for some reason not created? e.q. for notificationUrl validation failed
        if ($resourceId = $synchronization->resource_id) {
            $this->handleRequestExceptions(function () use ($synchronization, $resourceId) {
                Api::createDeleteRequest('/subscriptions/' . $resourceId)->execute();

                $this->synchronizations->unmarkAsWebhookSynchronizable($synchronization);
            });
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
            Api::createPatchRequest(
                $this->endpoint('/' . $eventId),
                new OutlookEventPayload($activity)
            )->execute();
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
            $response = Api::createPostRequest(
                $this->endpoint(),
                new OutlookEventPayload($activity)
            )->execute();

            $this->activities->addSynchronization(
                $activity,
                $response->getBody()['id'],
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
        $this->handleRequestExceptions(function () use ($activityId, $eventId) {
            Api::createDeleteRequest($this->endpoint('/' . $eventId))->execute();

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
     * Prepare the endpoint to retrieve the events
     *
     * @return string
     */
    protected function createEndpoint()
    {
        $startFromFormatted = (new \DateTime($this->calendar->startSyncFrom()))->format('Y-m-d\TH:i:s\Z');

        $endpoint = $this->endpoint();
        $endpoint .= '?$filter=createdDateTime ge ' . $startFromFormatted;
        $endpoint .= ' and type eq \'singleInstance\'';
        $endpoint .= ' and isDraft eq false';

        return $endpoint;
    }

    /**
     * Helper function to handle the requests common exception
     *
     * @param \Closure $callable
     *
     * @return void
     */
    protected function handleRequestExceptions(\Closure $callable)
    {
        Api::connectUsing($this->calendar->email);

        try {
            $callable();
        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        } catch (IdentityProviderException $e) {
            $this->accounts->setRequiresAuthentication($this->calendar->access_token_id);
        }
    }

    /**
     * Create new Microsoft Subscription instance
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return \Microsoft\Graph\Model\Subscription
     */
    protected function createSubscriptionInstance($synchronization)
    {
        return (new Subscription)->setChangeType('created,updated,deleted')
            ->setNotificationUrl($this->webHookUrl)
            ->setClientState($synchronization->id) // uuid;
            // https://docs.microsoft.com/en-us/graph/api/resources/subscription?view=graph-rest-1.0#maximum-length-of-subscription-per-resource-type
            ->setExpirationDateTime(now()->addDays(2))
            ->setResource($this->endpoint());
    }

    /**
     * Check whether the given exception message is invalid url
     *
     * @param string $message
     *
     * @return boolean
     */
    protected function isInvalidExceptionUrlMessage($message)
    {
        return stripos($message, 'invalid notification url') ||
                    stripos($message, 'subscription validation request failed') ||
                    stripos($message, '\'http\' is not supported') ||
                    stripos($message, 'the remote name could not be resolved');
    }

    /**
     * Create endpoint for the calendar
     *
     * @param string $glue
     *
     * @return string
     */
    protected function endpoint($glue = '')
    {
        return '/me/calendars/' . $this->calendar->calendar_id . '/events' . $glue;
    }
}
