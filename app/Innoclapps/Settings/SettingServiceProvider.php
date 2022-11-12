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

namespace App\Innoclapps\Settings;

use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Settings\Contracts\Store as StoreContract;
use App\Innoclapps\Settings\Contracts\Manager as ManagerContract;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(ManagerContract::class, SettingsManager::class);

        $this->app->singleton(StoreContract::class, function ($app) {
            return $app[ManagerContract::class]->driver();
        });

        $this->app->extend(ManagerContract::class, function (ManagerContract $manager, $app) {
            foreach ($app['config']->get('settings.drivers', []) as $driver => $params) {
                $manager->registerStore($driver, $params);
            }

            return $manager;
        });
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        if (! Innoclapps::isAppInstalled()) {
            return;
        }

        $this->app[ManagerContract::class]->driver()->configureOverrides();
    }
}
