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

use Illuminate\Support\Str;
use App\Innoclapps\Facades\Menu;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Innoclapps\Settings\SettingsMenu;
use App\Innoclapps\Settings\SettingsMenuItem;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (Innoclapps::isAppInstalled()) {
            $this->setUserDefinedExtensionsInConfig();
        }

        Innoclapps::booting(function () {
            Menu::register(MenuItem::make(__('settings.settings'), '/settings', 'Cog')
                ->canSeeWhen('is-super-admin')
                ->position(100));

            tap(SettingsMenuItem::make(__('fields.fields'))->icon('ViewGridAdd')->order(10), function ($item) {
                Innoclapps::registeredResources()
                    ->filter(fn ($resource) => $resource::$fieldsCustomizable)
                    ->each(function ($resource) use ($item) {
                        $item->withChild(
                            SettingsMenuItem::make(
                                $resource->singularLabel(),
                                "/settings/fields/{$resource->name()}"
                            ),
                            'fields-' . $resource->name()
                        );
                    });
                SettingsMenu::register($item, 'fields');
            });

            SettingsMenu::register(
                SettingsMenuItem::make(__('app.integrations'))->icon('Globe')->order(20)
                    ->withChild(SettingsMenuItem::make('Pusher', '/settings/integrations/pusher'), 'pusher')
                    ->withChild(SettingsMenuItem::make('Microsoft', '/settings/integrations/microsoft'), 'microsoft')
                    ->withChild(SettingsMenuItem::make('Google', '/settings/integrations/google'), 'google')
                    ->withChild(SettingsMenuItem::make('Twilio', '/settings/integrations/twilio'), 'twilio')
                    ->withChild(SettingsMenuItem::make('Zapier', '/settings/integrations/zapier'), 'zapier'),
                'integrations'
            );

            SettingsMenu::register(
                SettingsMenuItem::make(__('form.forms'), '/settings/forms', 'MenuAlt3')->order(30),
                'web-forms'
            );

            SettingsMenu::register(
                SettingsMenuItem::make(__('workflow.workflows'), '/settings/workflows', 'Adjustments')->order(40),
                'workflows'
            );

            SettingsMenu::register(
                SettingsMenuItem::make(__('mail_template.mail_templates'), '/settings/mailables', 'Mail')->order(50),
                'mailables'
            );

            SettingsMenu::register(
                SettingsMenuItem::make(__('settings.security.security'))->icon('ShieldCheck')->order(60)
                    ->withChild(SettingsMenuItem::make(__('settings.general'), '/settings/security'), 'security')
                    ->withChild(SettingsMenuItem::make(__('settings.recaptcha.recaptcha'), '/settings/recaptcha'), 'recaptcha'),
                'security'
            );

            SettingsMenu::register(
                SettingsMenuItem::make(__('settings.system'))->icon('Cog')->order(70)
                    ->withChild(SettingsMenuItem::make(__('update.update'), '/settings/update'), 'update')
                    ->withChild(SettingsMenuItem::make(__('settings.tools.tools'), '/settings/tools'), 'tools')
                    ->withChild(SettingsMenuItem::make(__('settings.translator.translator'), '/settings/translator'), 'translator')
                    ->withChild(SettingsMenuItem::make(__('app.system_info'), '/settings/info'), 'system-info')
                    ->withChild(SettingsMenuItem::make('Logs', '/settings/logs'), 'system-logs'),
                'system'
            );
        });
    }

    /**
    * Set application allowed media extensions
    */
    protected function setUserDefinedExtensionsInConfig()
    {
        // Replace dots with empty in case the user add dot in the extension name
        $this->app['config']->set('mediable.allowed_extensions', array_map(
            fn ($extension) => trim(Str::replaceFirst('.', '', $extension)),
            // use the get method because of 1.0.6 changes in settings function, fails on update if not used
            explode(',', settings()->get('allowed_extensions'))
        ));
    }
}
