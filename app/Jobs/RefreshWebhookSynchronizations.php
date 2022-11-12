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

namespace App\Jobs;

use App\Enums\SyncState;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contracts\Repositories\SynchronizationRepository;
use App\Calendar\Exceptions\InvalidNotificationURLException;

class RefreshWebhookSynchronizations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param \App\Contracts\Repositories\SynchronizationRepository $repository
     *
     * @return void
     */
    public function handle(SynchronizationRepository $repository)
    {
        tap($repository->withoutOAuthAuthenticationRequired()
            ->with('synchronizable')
            ->scopeQuery(function ($query) {
                return $query->where('sync_state', SyncState::ENABLED)
                    ->where(function ($query) {
                        return $query->whereNull('expires_at')
                            ->orWhere('expires_at', '<', now()->addDays(2));
                    })->whereNotNull('resource_id');
            })->get(), function () use ($repository) {
                $repository->resetScope();
            })->each(function ($synchronization) use ($repository) {
                try {
                    $synchronization->refreshWebhook();
                } catch (InvalidNotificationURLException $e) {
                    $repository->stopSync(
                        $synchronization->getKey(),
                        'We were unable to verify the notification URL for changes, make sure that your installation is publicly accessible, your installation URL starts with "https" and using valid SSL certificate.'
                    );
                }
            });
    }
}
