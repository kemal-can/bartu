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

namespace App\Innoclapps\Translation;

use App\Innoclapps\Translation\Loaders\OverrideFileLoader;
use App\Innoclapps\Contracts\Translation\TranslationLoader;
use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->bind(TranslationLoader::class, function ($app) {
            return new OverrideFileLoader([
                'path'            => $app['config']->get('innoclapps.lang.custom'),
                'lang_path'       => $app['path.lang'],
                'fallback_locale' => $app['config']->get('app.fallback_locale'),
            ]);
        });
    }

    /**
     * Register the translation line loader. This method registers a
     * `LoaderManager` instead of a simple `FileLoader` as the
     * applications `translation.loader` instance.
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new LoaderManager($app['files'], $app['path.lang']);
        });
    }
}
