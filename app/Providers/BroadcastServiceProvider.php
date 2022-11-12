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

namespace App\Providers;

use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Innoclapps::isAppInstalled()) {
            $this->configureBroadcasting();
        }

        Broadcast::routes();

        require base_path('routes/channels.php');
    }

    /**
     * Set the broadcasting driver
     */
    protected function configureBroadcasting()
    {
        if (Innoclapps::hasBroadcastingConfigured()) {
            $this->app['config']->set('broadcasting.default', 'pusher');
        }
    }
}
