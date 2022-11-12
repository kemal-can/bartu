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

namespace App\Innoclapps\OAuth;

use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Contracts\OAuth\StateStorage;
use App\Innoclapps\OAuth\State\StateStorageManager;
use Illuminate\Contracts\Support\DeferrableProvider;

class OAuthServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StateStorage::class, function ($app) {
            return new StateStorageManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [StateStorage::class];
    }
}
