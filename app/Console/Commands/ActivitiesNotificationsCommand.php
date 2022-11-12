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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\ActivityReminder;
use App\Contracts\Repositories\ActivityRepository;

class ActivitiesNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bartu:activities-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies owners of due activities.';

    /**
     * Execute the console command.
     *
     * @param \App\Contracts\Repositories\ActivityRepository $repository
     *
     * @return void
     */
    public function handle(ActivityRepository $repository)
    {
        foreach ($this->getReminderableActivities($repository) as $activity) {
            try {
                $activity->user->notify(new ActivityReminder($activity));
            } finally {
                // To avoid infinite loops in case there are error, we will
                // mark the activity as notified
                $repository->unguarded(function ($instance) use ($activity) {
                    $instance->update(['reminded_at' => now()], $activity->getKey());
                });
            }
        }
    }

    /**
     * Get the activities which are due for reminder
     *
     * @param \App\Contracts\Repositories\ActivityRepository $repository
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getReminderableActivities($repository)
    {
        return $repository->with(['user', 'type'])->reminderAble();
    }
}
