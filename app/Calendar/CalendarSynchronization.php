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
use Illuminate\Support\Arr;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Facades\ChangeLogger;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\ContactRepository;
use App\Contracts\Repositories\ActivityRepository;

class CalendarSynchronization
{
    /**
     * Create or update activity
     *
     * @param array $data
     * @param int $activityTypeId
     * @param \App\Models\User $forUser
     * @param string $eventId
     * @param int $calendarId
     *
     * @return array
     */
    protected function processViaChange(array $data, $activityTypeId, $forUser, $eventId, $calendarId)
    {
        $repository = resolve(ActivityRepository::class);
        User::setAssigneer($forUser);

        ChangeLogger::disable();

        $guests = Arr::pull($data, 'guests', []);

        $model = tap($this->activityModelFromEvent(
            $eventId,
            $calendarId,
            $data,
            $forUser,
            $activityTypeId,
            $repository
        ), function ($instance) {
            $instance->save();
        });

        if ($model->wasRecentlyCreated) {
            $repository->addSynchronization($model, $eventId, $calendarId);
        }

        $guestsChange = $this->saveGuests(
            $this->determineGuestsForSaving($guests, $forUser),
            $repository,
            $model
        );

        ChangeLogger::enable();

        return [$model, count(array_filter($guestsChange)) !== 0];
    }

    /**
     * Get the activity model from event
     *
     * @param int $eventId
     * @param int $calendarId
     * @param array $attributes
     * @param \App\Models\User $forUser
     * @param int $activityTypeId
     *
     * @return \App\Models\Activity
     */
    protected function activityModelFromEvent(
        $eventId,
        $calendarId,
        array $attributes,
        $forUser,
        $activityTypeId,
        $repository,
    ) {
        $instance = $repository->findByCalendarSynchronization($eventId, $calendarId);

        return ($instance ?: new Activity)->forceFill(array_merge($attributes, $instance ? [] : [
            'user_id'             => $forUser->getKey(),
            'created_by'          => $forUser->getKey(),
            'owner_assigned_date' => now(),
            'activity_type_id'    => $activityTypeId,
        ]));
    }

    /**
     * Persists the guests in storage
     *
     * @param \Illuminate\Support\Collection $guests
     * @param \App\Contracts\Repositories\ActivityRepository $repository
     * @param \App\Models\Activity $activity
     *
     * @return array
     */
    protected function saveGuests($guests, $repository, $activity)
    {
        $changes      = $repository->saveGuestsWithoutNotifications($activity, $guests->all());
        $associations = collect([]);

        // We will check if the activity is new, if yes, we will associate
        // the activity to all contacts that were added as attendee
        if ($activity->wasRecentlyCreated) {
            $associations = $associations->merge($guests->whereInstanceOf(Contact::class));
        } else {
            // If the activity is not new, we will only associate the newly guest contacts
            // we associate only the newly guest contacts because the user may have dissociated
            // any contacts from the activity after it was added and we should respect that not re-associate the contacts again
            $associations = $associations->merge(
                collect($changes['attached'])->whereInstanceOf(Contact::class)
            );
        }

        if ($associations->isNotEmpty()) {
            $repository->syncWithoutDetaching($activity->getKey(), 'contacts', $associations->pluck('id'));
        }

        return $changes;
    }

    /**
     * Determine the guests for saving when processing the changed events
     *
     * @param array $guests
     * @param \App\Models\User $forUser
     *
     * @return \Illuminate\Support\Collection
     */
    protected function determineGuestsForSaving($guests, $forUser)
    {
        $users             = resolve(UserRepository::class)->all();
        $contactRepository = resolve(ContactRepository::class);

        return collect($guests)->reject(function ($attendee) {
            // For all cases, remove any guests without email
            return empty($attendee['email']);
        })->map(function ($attendee) use ($users, $contactRepository, $forUser) {
            if ($user = $users->firstWhere('email', $attendee['email'])) {
                return $user;
            } elseif ($contact = $contactRepository->findByEmail($attendee['email'])) {
                return $contact;
            }

            return $contactRepository->unguarded(function ($repository) use ($attendee, $forUser) {
                $name = $attendee['name'];

                $firstName = null;
                $lastName = null;

                if (! $name) {
                    $firstName = $attendee['email'];
                } else {
                    $nameArray = explode(' ', $name);
                    $firstName = $nameArray[0];
                    $lastName = $nameArray[1] ?? null;
                }

                $contact = $repository->create([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $attendee['email'],
                    'created_by' => $forUser->getKey(),
                    'user_id'    => $forUser->getKey(),
                ]);

                $this->addChangelogGuestCreatedAsContact($contact, $forUser);

                return $contact;
            });
        });
    }

    /**
     * Add changelog when a guest is created as contact
     *
     * @param \App\Models\Contact $contact
     * @param \App\Models\User $user
     */
    protected function addChangelogGuestCreatedAsContact($contact, $user)
    {
        ChangeLogger::forceLogging()
            ->useModelLog()
            ->on($contact)
            ->byAnonymous()
            ->generic()
            ->withProperties([
                'icon' => 'Calendar',
                'lang' => [
                    'key'   => 'contact.timeline.imported_via_calendar_attendee',
                    'attrs' => [
                        'user' => $user->name,
                    ],
                ],
        ])->log();
    }
}
