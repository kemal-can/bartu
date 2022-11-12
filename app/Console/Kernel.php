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

namespace App\Console;

use App\Support\SyncNextActivity;
use App\Jobs\PeriodicSynchronizations;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\RefreshWebhookSynchronizations;
use App\Innoclapps\Facades\MailableTemplates;
use App\Console\Commands\EmailAccountsSyncCommand;
use App\Innoclapps\Media\PruneStaleMediaAttachments;
use App\Console\Commands\ActivitiesNotificationsCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            settings()->set('last_cron_run', now());
        })->after(function () {
            settings()->save();
        })->everyMinute();

        // Not needed?
        $schedule->call(function () {
            MailableTemplates::seedIfRequired();
        })->daily();

        $schedule->command('model:prune')->daily();

        $schedule->call(new PruneStaleMediaAttachments)->name(PruneStaleMediaAttachments::class)->daily();

        $schedule->call(new SyncNextActivity)->everyMinute()->name(SyncNextActivity::class)->withoutOverlapping(5);

        $schedule->command(ActivitiesNotificationsCommand::class)->everyMinute()->withoutOverlapping(5);

        $schedule->command(EmailAccountsSyncCommand::class, ['--broadcast'])
            ->{$this->syncMethodFromConfigValue(config('app.mail_client.sync.every'))}()
            ->withoutOverlapping(5)
            ->before(fn () => EmailAccountsSyncCommand::setLock())
            ->after(fn ()  => EmailAccountsSyncCommand::removeLock())
            ->sendOutputTo(storage_path('logs/email-accounts-sync.log'));

        $schedule->command('queue:flush')->weekly();

        $schedule->job(PeriodicSynchronizations::class)->{$this->syncMethodFromConfigValue(config('app.sync.every'))}();

        $schedule->job(RefreshWebhookSynchronizations::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the sync method from option
     *
     * @param string $value
     *
     * @return string
     */
    protected function syncMethodFromConfigValue($value)
    {
        return match ($value) {
            'hourly' => 'hourly',
            default  => 'every' . ucfirst($value),
        };
    }
}
