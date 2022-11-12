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

use App\Enums\SyncState;
use App\Models\Calendar;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CalendarRepository;
use App\Contracts\Repositories\SynchronizationRepository;
use App\Calendar\Exceptions\InvalidNotificationURLException;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class CalendarRepositoryEloquent extends AppRepository implements CalendarRepository
{
    /**
     * @var array
     */
    protected $messages = [
        'invalid_url'         => 'We were unable to verify the notification URL for changes, make sure that your installation is publicly accessible, your installation URL starts with "https" and using valid SSL certificate.',
        'oauth_requires_auth' => 'Synchronization disabled because the OAuth account requires authentication',
    ];

    /**
     * @var \App\Contracts\Repositories\SynchronizationRepository
     */
    protected $synchronizations;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Calendar::class;
    }

    /**
     * Get the connected OAuth calendar for the given user
     *
     * @param \App\Models\User $user
     *
     * @return \App\Models\Calendar|null
     */
    public function forUser($user)
    {
        return $this->whereHas('user', function ($query) use ($user) {
            return $query->where('user_id', $user->getKey());
        })->whereHas('synchronization', function ($query) {
            return $query->where('sync_state', '!=', SyncState::DISABLED);
        })->orderBy('created_at')->first();
    }

    /**
     * Save the calendar for the user
     *
     * @param array $data
     * @param \App\Models\User $user
     *
     * @return \App\Models\Calendar
     */
    public function save(array $payload, $user)
    {
        $originalCalendar = $this->forUser($user);

        $calendar = $this->findByField(
            'calendar_id',
            $payload['calendar_id']
        )->first() ?: new Calendar;

        try {
            $this->fillForEvent($calendar, $payload, $user)->save();
            $this->syncRepository()->enableSync($calendar->synchronization);
        } catch (InvalidNotificationURLException $e) {
            $this->syncRepository()->stopSync(
                $calendar->synchronization,
                $this->messages['invalid_url']
            );
        }

        if ($originalCalendar && $payload['calendar_id'] != $originalCalendar->calendar_id) {
            $this->disableSync($originalCalendar);
        }

        return $calendar->fresh()->load('oAuthAccount');
    }

    /**
     * Disable the sync for the given calendar
     *
     * @param int\App\Models\Calendar $calendar
     *
     * @return void
     */
    public function disableSync($calendar)
    {
        $model = $calendar instanceof Calendar ?
            $calendar :
            $this->find($calendar);

        $this->syncRepository()->disableSync(
            $model->synchronization
        );

        $this->unguarded(function ($repository) use ($model) {
            $repository->update([
                'access_token_id' => null,
            ], $model->id);
        });
    }

    /**
     * Fill the calendar with data for event
     *
     * @param \App\Models\Calendar $calendar
     * @param array $payload
     * @param \App\Models\User $user
     *
     * @return \App\Models\Calendar
     */
    protected function fillForEvent($calendar, $payload, $user)
    {
        return $calendar->forceFill([
            'calendar_id'      => $payload['calendar_id'],
            'activity_type_id' => $payload['activity_type_id'],
            'activity_types'   => $payload['activity_types'],
            'access_token_id'  => $payload['access_token_id'],
            'email'            => app(OAuthAccountRepository::class)
                ->find($payload['access_token_id'])->email,
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * Get the synchronization repository
     *
     * @return \App\Contracts\Repositories\SynchronizationRepository
     */
    protected function syncRepository()
    {
        if (! $this->synchronizations) {
            $this->synchronizations = resolve(SynchronizationRepository::class);
        }

        return $this->synchronizations;
    }
}
