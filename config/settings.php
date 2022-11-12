<?php

return [
    /* -----------------------------------------------------------------
     |  Default drivers
     | -----------------------------------------------------------------
     | Supported: 'array', 'json', 'database', 'redis'
     |
     | When using the "override" feature, the database driver should not be used
     | as it's interacting with the database in the service provider, it works good, but it's not recommended
     */

    'default' => ENV('SETTINGS_DRIVER', 'json'),

    /* -----------------------------------------------------------------
     |  Drivers
     | -----------------------------------------------------------------
     */

    'drivers' => [

        'array' => [
            'driver' => App\Innoclapps\Settings\Stores\ArrayStore::class,
        ],

        'json' => [
            'driver' => App\Innoclapps\Settings\Stores\JsonStore::class,

            'options' => [
                'path' => storage_path('settings.json'),
            ],
        ],

        'database' => [
            'driver' => \App\Innoclapps\Settings\Stores\DatabaseStore::class,

            'options' => [
                'table' => 'settings',
                'model' => \App\Innoclapps\Models\Setting::class,
            ],
        ],

        'redis' => [
            'driver' => App\Innoclapps\Settings\Stores\RedisStore::class,

            'options' => [
                'client' => 'predis',

                'default' => [
                    'host'     => env('REDIS_HOST', '127.0.0.1'),
                    'port'     => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DB', 0),
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Override application config values
    |--------------------------------------------------------------------------
    |
    | If defined, settings package will override these config values.
    |
    | Sample:
    |   "app.locale" => "settings.locale",
    |
    */
    'override' => [
        'app.name' => 'company_name',

        'innoclapps.date_format' => 'date_format',
        'innoclapps.time_format' => 'time_format',
        'innoclapps.currency'    => 'currency',

        'innoclapps.microsoft.client_id'     => 'msgraph_client_id',
        'innoclapps.microsoft.client_secret' => 'msgraph_client_secret',

        'innoclapps.google.client_id'     => 'google_client_id',
        'innoclapps.google.client_secret' => 'google_client_secret',

        'innoclapps.recaptcha.site_key'    => 'recaptcha_site_key',
        'innoclapps.recaptcha.secret_key'  => 'recaptcha_secret_key',
        'innoclapps.recaptcha.ignored_ips' => 'recaptcha_ignored_ips',

        'innoclapps.services.twilio.applicationSid' => 'twilio_app_sid',
        'innoclapps.services.twilio.accountSid'     => 'twilio_account_sid',
        'innoclapps.services.twilio.authToken'      => 'twilio_auth_token',
        'innoclapps.services.twilio.number'         => 'twilio_number',

        'broadcasting.connections.pusher.key'             => 'pusher_app_key',
        'broadcasting.connections.pusher.secret'          => 'pusher_app_secret',
        'broadcasting.connections.pusher.app_id'          => 'pusher_app_id',
        'broadcasting.connections.pusher.options.cluster' => 'pusher_app_cluster',
        'updater.purchase_key'                            => 'purchase_key',

        'app.logo.light' => 'logo_light',
        'app.logo.dark'  => 'logo_dark',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encrypted settings keys
    |--------------------------------------------------------------------------
    |
    | Define settings keys which value should be encrypted in the store
    |
    */
    'encrypted' => [
        'msgraph_client_secret',
        'google_client_secret',
        'twilio_auth_token',
    ],
];
