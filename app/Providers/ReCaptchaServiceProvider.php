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

use App\Innoclapps\ReCaptcha;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class ReCaptchaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('recaptcha', function ($app) {
            return (new ReCaptcha(Request::instance()))
                ->setSiteKey($app['config']->get('innoclapps.recaptcha.site_key'))
                ->setSecretKey($app['config']->get('innoclapps.recaptcha.secret_key'))
                ->setSkippedIps($app['config']->get('innoclapps.recaptcha.ignored_ips', []));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['recaptcha'];
    }
}
