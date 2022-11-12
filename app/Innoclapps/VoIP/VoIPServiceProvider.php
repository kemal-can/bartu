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

namespace App\Innoclapps\VoIP;

use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Contracts\VoIP\VoIPClient;
use Illuminate\Contracts\Support\DeferrableProvider;

class VoIPServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(VoIPClient::class, function ($app) {
            return new VoIPManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [VoIPClient::class];
    }
}
