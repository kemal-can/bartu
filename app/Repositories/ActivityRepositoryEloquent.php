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

namespace App\Repositories;

use App\Models\Activity;
use App\Models\Calendar;
use Illuminate\Support\Arr;
use App\Models\ActivityType;
use Illuminate\Support\Carbon;
use App\Contracts\Attendeeable;
use App\Support\PendingMention;
use Illuminate\Support\Facades\Mail;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Contracts\Mail\Mailable;
use App\Innoclapps\Repository\SoftDeletes;
use Illuminate\Notifications\Notification;
use App\Innoclapps\Repository\AppRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\UserRepository;
use App\Innoclapps\Criteria\WithTrashedCriteria;
use App\Contracts\Repositories\CompanyRepository;
use App\Contracts\Repositories\ContactRepository;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\CalendarRepository;
use App\Criteria\Activity\IncompleteActivitiesCriteria;
use App\Criteria\Activity\NotRemindedActivitiesCriteria;
use App\Criteria\Activity\ReminderableActivitiesCriteria;
use App\Criteria\Activity\IncompleteActivitiesByUserCriteria;

class ActivityRepositoryEloquent extends AppRepository implements ActivityRepository
{
    use SoftDeletes;

    /**
     * Delete from calendar
     *
     * @var boolean
     */
    public $deleteFromCalendar = true;

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'title' => 'like',
        'note'  => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Activity::class;
    }

    /**
    * Save a new entity in repository
    *
    * @param array $attributes
    *
    * @return mixed
    */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance();

        $mention = new PendingMention($attributes['note'] ?? '');

        if ($mention->hasMentions()) {
            $attributes['note'] = $mention->getUpdatedText();
        }

        $attributes['end_date'] ??= $attributes['due_date'];

        if (! array_key_exists('activity_type_id', $attributes)) {
            $attributes['activity_type_id'] = ActivityType::getDefaultType();
        }

        $model->fill($attributes);

        if (($attributes['is_completed'] ?? null) === true) {
            $model->completed_at = now();
        }

        $this->performInsert($model, $attributes);

        $this->resetModel();

        if (isset($attributes['guests'])) {
            $this->saveGuests($model, $this->parseGuestsForSave($attributes['guests']));
        }

        $this->notifyMentionedUsers(
            $mention,
            $model,
            $attributes['via_resource'] ?? null,
            $attributes['via_resource_id'] ?? null
        );

        if ($model->user_id && $model->user->canSyncToCalendar() &&
                $model->typeCanBeSynchronizedToCalendar()) {
            dispatch(function () use ($model) {
                $model->user->calendar->synchronizer()->createEvent($model);
            });
        }

        return $this->parseResult($model);
    }

    /**
     * Update activity
     *
     * @param array $attributes
     * @param mixed $id
     *
     * @return \App\Models\EmailAccountFolder
     */
    public function update(array $attributes, $id)
    {
        $mention = new PendingMention($attributes['note'] ?? '');

        if ($mention->hasMentions()) {
            $attributes['note'] = $mention->getUpdatedText();
        }

        $this->makeModel();
        $this->applyScope();

        $activity     = $this->model->findOrFail($id);
        $isCompleted  = $activity->isCompleted;
        $originalUser = $activity->user;

        $markAsCompleted = Arr::pull($attributes, 'is_completed');
        $activity->fill($attributes);

        if (! $isCompleted && $markAsCompleted === true) {
            $activity->completed_at = now();
        } elseif ($isCompleted && $markAsCompleted === false) {
            $activity->completed_at = null;
        }

        $this->performUpdate($activity, $attributes);
        $this->resetModel();

        if (isset($attributes['guests'])) {
            $this->saveGuests($activity, $this->parseGuestsForSave($attributes['guests']));
        }

        $this->notifyMentionedUsers(
            $mention,
            $activity,
            $attributes['via_resource'] ?? null,
            $attributes['via_resource_id'] ?? null
        );

        // Refresh the model so the user relation is reloaded on next access
        // as we are first accessing the relation to get the original user and
        // Laravel will cache the relation that don't exist in case there is no original user
        // in this case on the code below, the user won't be available even when provided via the $attributes
        $activity->refresh();

        if (($originalUser && ! $activity->user_id) || // owner set to empty
                ($originalUser && $activity->user_id !== $originalUser->getKey())) { // owner changed
            $this->maybeDeleteEventFromCalendar($activity, $originalUser);
        }

        if ((($originalUser && $activity->user_id && $activity->user_id != $originalUser->getKey() ||  // owner changed
                ! $originalUser && $activity->user_id)) && // new owner
             $activity->user->canSyncToCalendar() &&
             $activity->typeCanBeSynchronizedToCalendar()) {
            dispatch(function () use ($activity) {
                $activity->user->calendar->synchronizer()->createEvent($activity);
            });
        } elseif ($activity->user_id &&
                $activity->isSynchronizedToCalendar($activity->user->calendar) &&
                $activity->user->canSyncToCalendar() &&
                $activity->typeCanBeSynchronizedToCalendar()) {
            dispatch(function () use ($activity) {
                $activity->user->calendar->synchronizer()->updateEvent(
                    $activity,
                    $activity->latestSynchronization()->pivot->event_id
                );
            });
        }

        return $this->parseResult($activity);
    }

    /**
    * Notify the mentioned users for the given mention
    *
    * @param \App\Support\PendingMention $mention
    * @param \App\Models\Activity $activity
    * @param string|null $viaResource
    * @param int|null $viaResourceId
    *
    * @return void
    */
    protected function notifyMentionedUsers(PendingMention $mention, $activity, $viaResource = null, $viaResourceId = null)
    {
        $intermediate = $viaResource && $viaResourceId ?
            Innoclapps::resourceByName($viaResource)->repository()->find($viaResourceId) :
            $activity;

        $mention->setUrl($intermediate->path)->withUrlQueryParameter([
            'section'    => $viaResource && $viaResourceId ? $activity->resource()->name() : null,
            'resourceId' => $viaResource && $viaResourceId ? $activity->getKey() : null,
        ])->notify();
    }

    /**
     * Parse the given guests array for save
     *
     * @param array $guests
     *
     * @return array
     */
    protected function parseGuestsForSave($guests) : array
    {
        return with([], function ($instance) use ($guests) {
            foreach ($guests as $resourceName => $ids) {
                $instance = array_merge(
                    $instance,
                    Innoclapps::resourceByName($resourceName)->repository()->findMany($ids)->all()
                );
            }

            return $instance;
        });
    }

    /**
     * Delete the activity synchronization data
     *
     * @param \App\Models\Activity $activity
     * @param string $eventId
     * @param int $calendarId
     *
     * @return void
     */
    public function deleteSynchronization($activity, $eventId, $calendarId)
    {
        $activity->synchronizations()
            ->where('event_id', $eventId)
            ->detach($calendarId);
    }

    /**
     * Delete the activity synchronization data
     *
     * @param \App\Models\Activity $activity
     * @param string $eventId
     * @param int $calendarId
     *
     * @return void
     */
    public function addSynchronization($activity, $eventId, $calendarId)
    {
        $activity->synchronizations()->attach($calendarId, [
            'event_id'        => $eventId,
            'synchronized_at' => now(),
        ]);
    }

    /**
     * Find activity by calendar synchronization
     *
     * @param string $eventId
     * @param int|\App\Models\Calendar $calendarId
     *
     * @return \App\Models\Activity|null
     */
    public function findByCalendarSynchronization($eventId, $calendarId)
    {
        $calendar = $calendar = ! $calendarId instanceof Calendar ?
            resolve(CalendarRepository::class)->find($calendarId) :
            $calendarId;

        return $this->whereHas('synchronizations', function ($query) use ($eventId, $calendar) {
            return $query->where('event_id', $eventId)
                ->where('activity_calendar_sync.calendar_id', $calendar->getKey());
        })->first();
    }

    /**
     * Tap the calendar request query
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function tapCalendarQuery($query, $request)
    {
        $user = $request->user();

        if ($request->user_id && $user->can('view all activities')) {
            $user = resolve(UserRepository::class)->find($request->user_id);
        }

        $query->incomplete()->where('user_id', $user->id);

        if ($request->filled('activity_type_id')) {
            $query->where('activity_type_id', $request->activity_type_id);
        }

        $query->with('type');
    }

    /**
     * Tap the calendar date query
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $startColumn
     * @param string $endColumn
     * @param \Illuminate\Http\Reques $request
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function tapCalendarDateQuery($query, $startColumn, $endColumn, $request)
    {
        $query->orWhere(function ($query) use ($request) {
            $startInUserTimezone = Carbon::parse($request->start_date)->tz($request->user()->timezone);
            $endInUserTimezone = Carbon::parse($request->end_date)->tz($request->user()->timezone);

            $startFormatted = $startInUserTimezone->format('Y-m-d');
            $endFormatted = $endInUserTimezone->format('Y-m-d');

            // https://stackoverflow.com/questions/17014066/mysql-query-to-select-events-between-start-end-date
            $spanRaw = "'{$startFormatted}' BETWEEN due_date AND end_date";

            return $query->whereRaw(
                "CASE
                    WHEN due_time IS NULL AND end_time IS NULL THEN due_date BETWEEN
                    '{$startFormatted}' AND '{$endFormatted}' OR {$spanRaw}
                    WHEN due_time IS NOT NULL AND end_time IS NULL THEN due_date
                    BETWEEN '{$request->start_date}' AND {$endFormatted} OR {$spanRaw}
                END"
            );
        });
    }

    /**
     * Save activity guests
     *
     * @link https://stackoverflow.com/questions/34809469/laravel-many-to-many-relationship-to-multiple-models
     *
     * @param \App\Models\Activity $activity
     * @param \App\Contracts\Attendeeable[] $guests
     * @param boolean $notify
     *
     * @return array
     */
    public function saveGuests($activity, $guests, $notify = false)
    {
        $current = $activity->guests()->with('guestable')->get();

        // First, we will check the guests that we need to detach by
        // checking if the current guestable does not exists in the actual provided guests
        $detach = $current->filter(function ($current) use ($guests) {
            return ! collect($guests)->first(function ($guest) use ($current) {
                return $guest->getKey() === $current->guestable->getKey() &&
                $current->guestable::class === $guest::class;
            });
        });

        // Next we will check the new guests we need to attach by checking if the
        // provided guestable does not exists in the current guests
        $attach = collect($guests)->filter(function ($guest) use ($current) {
            return ! $current->first(function ($current) use ($guest) {
                return $guest->getKey() === $current->guestable->getKey() &&
                $current->guestable::class === $guest::class;
            });
        })->all();

        $this->addGuest($activity, $attach, $notify);
        $detach->each->delete();

        return ['attached' => $attach, 'detached' => $detach->all()];
    }

    /**
     * Save activity guests without actually trying to send notification
     *
     * @param \App\Models\Activity $activity
     * @param \App\Contracts\Attendeeable[] $guests
     *
     * @return array
     */
    public function saveGuestsWithoutNotifications($activity, $guests)
    {
        return $this->saveGuests($activity, $guests, true);
    }

    /**
     * Add new guest(s) to the given activity
     *
     * @param \App\Models\Activity $activity
     * @param \App\Contracts\Attendeeable|array $guests
     * @param boolean $notify
     *
     * @return void
     */
    public function addGuest(Activity $activity, Attendeeable|array $guests, bool $notify = false) : void
    {
        if ($guests instanceof Attendeeable) {
            $guests = [$guests];
        }

        // Todo, in future check if the actual guest exists
        foreach ($guests as $model) {
            $guest = $model->guests()->create([]);
            $guest->activities()->attach($activity);

            if (! $notify && $model->shouldSendAttendingNotification($model)) {
                $this->sendNotificationToAttendee($model, $activity);
            }
        }
    }

    /**
     * Send notification to the given attendee
     *
     * @param \App\Contracts\Attendeeable $guest
     * @param \App\Models\Activity $activity
     *
     * @return void
     */
    protected function sendNotificationToAttendee(Attendeeable $guest, Activity $activity)
    {
        $notification = $guest->getAttendeeNotificationClass();

        if (method_exists($guest, 'notify') && is_a($notification, Notification::class, true)) {
            $guest->notify(new $notification($guest, $activity));
        } elseif (is_a($notification, Mailable::class, true) && ! empty($email = $guest->getGuestEmail())) {
            Mail::to($email)->send(new $notification($guest, $activity));
        }
    }

    /**
     * Delete a activity in repository by id without making a request to remove it from the calendar
     *
     * @param mixed $id
     * @param boolean $deleteFromCalendar
     *
     * @return boolean
     */
    public function deleteWithoutRemovingFromCalendar($id)
    {
        return $this->delete($id, false);
    }

    /**
     * Delete user by a given id
     *
     * @param mixed $id
     * @param int|null $deleteFromCalendar
     *
     * @return boolean
     */
    public function delete($id, $deleteFromCalendar = true)
    {
        $this->deleteFromCalendar = $deleteFromCalendar;

        return tap(parent::delete($id), function () {
            $this->deleteFromCalendar = true;
        });
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::restored(function ($model) {
            if ($model->user_id && $model->user->canSyncToCalendar() &&
                $model->typeCanBeSynchronizedToCalendar()) {
                dispatch(function () use ($model) {
                    $model->user->calendar->synchronizer()->createEvent($model);
                });
            }
        });

        static::deleting(function ($model, $repository) {
            if ($model->isForceDeleting()) {
                $repository->purge($model, false);
            }

            if ($repository->deleteFromCalendar) {
                $repository->maybeDeleteEventFromCalendar($model, $model->user);
            }
        });
    }

    /**
     * Bulk delete activities by given activities or ids
     *
     * @param mixed $activity
     * @param boolean $deleteFromCalendar
     *
     * @return void
     */
    public function bulkDelete($activity, $deleteFromCalendar = true)
    {
        if ($activity instanceof Activity) {
            $activities = [$activity];
        } elseif (is_numeric($activity)) {
            $activities = [$this->find($activity)];
        } else {
            $activities = ! $activity instanceof Collection ? $this->findWhereIn('id', $activity) : $activity;
        }

        foreach ($activities as $activity) {
            if ($deleteFromCalendar) {
                $this->maybeDeleteEventFromCalendar($activity, $activity->user);
            }

            $activity->delete();
        }
    }

    /**
     * Purge the activity data
     *
     * @param \App\Models\Activity $activity
     * @param boolean $deleteFromCalendar
     *
     * @return void
     */
    protected function purge($activity, $deleteFromCalendar = true)
    {
        if ($deleteFromCalendar) {
            $this->maybeDeleteEventFromCalendar($activity, $activity->user);
        }

        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $activity->{$relation}()->withTrashed()->detach();
        }

        $activity->guests->each(function ($guest) {
            $guest->activities()->withTrashed()->detach();
            $guest->delete();
        });

        foreach ([ContactRepository::class, CompanyRepository::class, DealRepository::class] as $repository) {
            resolve($repository)->pushCriteria(WithTrashedCriteria::class)
                ->massUpdate(
                    ['next_activity_id' => null],
                    ['next_activity_id' => $activity->getKey()]
                );
        }
    }

    /**
     * Delete the activity event from calendar if needed
     *
     * @param \App\Models\Activity $activity
     * @param \App\Models\User $user
     *
     * @return void
     */
    protected function maybeDeleteEventFromCalendar($activity, $user)
    {
        if (! $user || ! $activity->isSynchronizedToCalendar($user->calendar)) {
            return;
        }

        $remoteEventId = $activity->latestSynchronization($user->calendar)->pivot->event_id;
        $activityId    = $activity->getKey();

        dispatch(function () use ($activityId, $remoteEventId, $user) {
            $user->calendar->synchronizer()->deleteEvent($activityId, $remoteEventId);
        });
    }

    /**
     * Get incomplete activities for user
     *
     * @param int|\App\Models\User $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getIncompleteByUser($user)
    {
        return tap($this->pushCriteria(new IncompleteActivitiesByUserCriteria($user))
            ->orderBy(Activity::dueDateQueryExpression())
            ->all(), function () {
                $this->popCriteria(IncompleteActivitiesByUserCriteria::class);
            });
    }

    /**
     * Mark the activity as completed
     *
     * @param \App\Models\Activity|int $activity
     *
     * @return \App\Models\Activity
     */
    public function complete($activity)
    {
        $activity = $activity instanceof Activity ? $activity : $this->find($activity);

        return $this->unguarded(function ($repository) use ($activity) {
            return $repository->update(['is_completed' => true], $activity->id);
        });
    }

    /**
     * Mark the activity as incomplete
     *
     * @param $activity \App\Models\Activity|int
     *
     * @return \App\Models\Activity
     */
    public function incomplete($activity)
    {
        $activity = $activity instanceof Activity ? $activity : $this->find($activity);

        return $this->unguarded(function ($repository) use ($activity) {
            return $repository->update(['is_completed' => false], $activity->id);
        });
    }

    /**
     * Get all overdue activities
     *
     * @todo, not used, perhaps remove?
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function overdue()
    {
        $this->model = $this->model->overdue();

        return $this->get();
    }

    /**
     * Get all due able activities that assigned user should be notified
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function reminderAble()
    {
        return tap($this->pushCriteria(IncompleteActivitiesCriteria::class)
            ->pushCriteria(NotRemindedActivitiesCriteria::class)
            ->pushCriteria(ReminderableActivitiesCriteria::class)
            ->whereHas('user')->get(), function () {
                $this->popCriteria([
                    IncompleteActivitiesCriteria::class,
                    NotRemindedActivitiesCriteria::class,
                    ReminderableActivitiesCriteria::class,
                ]);
            });
    }

    /**
     * The relations that are required for the responsee
     *
     * @return array
     */
    protected function eagerLoad()
    {
        $this->withCount(['comments']);

        return [
           'companies.nextActivity',
           'contacts.nextActivity',
           'deals.nextActivity',
           'media',
           'creator',
           'guests.guestable',
       ];
    }
}
