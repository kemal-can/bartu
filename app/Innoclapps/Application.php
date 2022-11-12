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

namespace App\Innoclapps;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Innoclapps\Contracts\Metable;
use App\Innoclapps\Updater\Migration;
use App\Innoclapps\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Process\PhpExecutableFinder;

class Application
{
    /**
     * The application version.
     *
     * @var string
     */
    const VERSION = '1.0.7';

    /**
     * System name that will be used over the system
     * E.q. for automated actions performed by the application or logs
     */
    const SYSTEM_NAME = 'System';

    /**
     * Indicates the installed file
     */
    const INSTALLED_FILE = '.installed';

    /**
     * Installation route prefix
     */
    const INSTALL_ROUTE_PREFIX = 'install';

    /**
     * The API prefix for the application
     */
    const API_PREFIX = 'api';

    /**
     * Indicates whether there is import in progress
     *
     * @var boolean
     */
    protected static bool $importStatus = false;

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected bool $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var array
     */
    protected array $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var array
     */
    protected array $bootedCallbacks = [];

    /**
     * Available notifications
     *
     * @var array
     */
    protected static array $notifications = [];

    /**
     * Stores the default notifications data when disabling the notifications
     * then later we can use to enable the notifications again with the default
     * values
     *
     * @var array
     */
    protected static array $disabledNotificationsConfig = [];

    /**
     * Registered resources
     *
     * @var \Illuminate\Collections\Collection
     */
    public static ?Collection $resources = null;

    /**
     * Provide data to views
     *
     * @var array
     */
    public static array $provideToScript = [];

    /**
     * All the custom registered scripts
     *
     * @var array
     */
    public static array $scripts = [];

    /**
     * All the custom registered styles
     *
     * @var array
     */
    public static array $styles = [];

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version() : string
    {
        return static::VERSION;
    }

    /**
     * The the system name
     *
     * @return string
     */
    public function systemName() : string
    {
        return static::SYSTEM_NAME;
    }

    /**
     * Determine if the application has booted.
     *
     * @return boolean
     */
    public function isBooted() : bool
    {
        return $this->booted;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot() : void
    {
        if ($this->booted) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Register a new boot listener.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function booting(callable $callback) : void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function booted(callable $callback) : void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param array $callbacks
     *
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks) : void
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Get the application favourite colors
     *
     * @return array
     */
    public static function favouriteColors() : array
    {
        return config('innoclapps.colors');
    }

    /**
     * Disable notifications
     *
     * broadcasting, mail
     *
     * @see enableNotifications
     *
     * @return void
     */
    public static function disableNotifications() : void
    {
        $defaults = [config('broadcasting.default'), config('mail.driver')];

        // Disable
        config(['broadcasting.default' => 'null']);
        config(['mail.driver' => 'array']);

        // Store the current values so we can use them to enable the notifications again
        static::$disabledNotificationsConfig['broadcasting.default'] = $defaults[0];
        static::$disabledNotificationsConfig['mail.driver']          = $defaults[1];
    }

    /**
     * Check whether the application has broadcasting configured
     *
     * @return boolean
     */
    public static function hasBroadcastingConfigured() : bool
    {
        $keyOptions = Arr::only(
            config('broadcasting.connections.pusher'),
            ['key', 'secret', 'app_id']
        );

        return count(array_filter($keyOptions)) === count($keyOptions);
    }

    /**
     * Enable notifications again
     * Used only after disableNotifications method is called
     *
     * @see disableNotifications
     *
     * @return void
     */
    public static function enableNotifications() : void
    {
        foreach (static::$disabledNotificationsConfig as $key => $value) {
            config([$key => $value]);
        }

        static::$disabledNotificationsConfig = [];
    }

    /**
     * Register the given notifications
     *
     * @return array $notifications
     *
     * @return void
     */
    public static function notifications(array $notifications) : void
    {
        static::$notifications = array_unique(
            array_merge(static::$notifications, $notifications)
        );
    }

    /**
     * Register the application notifications in the given directory
     *
     * @param string $directory
     *
     * @return void
     */
    public static function notificationsIn(string $directory) : void
    {
        static::notifications(
            Filesystem::listClassFilesOfSubclass(Notification::class, $directory)
        );
    }

    /**
     * Get all the notifications information for front-end
     *
     * @param \App\Innoclapps\Contracts\Metable $user|null
     *
     * @return array
     */
    public static function notificationsInformation(?Metable $user = null) : array
    {
        return collect(static::$notifications)->map(function ($notification) use ($user) {
            return array_merge([
                'key'         => $notification::key(),
                'name'        => $notification::name(),
                'description' => $notification::description(),

                'channels'                 => $channels = collect($notification::availableChannels())
                    ->reject(fn ($channel) => $channel === 'broadcast')->values(),

            ], is_null($user) ? [] : ['availability' => array_merge(
                $channels->mapWithKeys(fn ($channel) => [$channel => true])->all(),
                static::notificationSettings($notification::key(), $user)
            )]);
        })->all();
    }

    /**
     * Get notification applied user settings
     *
     * @param string $key
     * @param \App\Innoclapps\Contracts\Metable $user
     *
     * @return array
     */
    public static function notificationSettings($key, Metable $user) : array
    {
        return $user->getMeta('notification-settings', [])[$key] ?? [];
    }

    /**
     * Update the user notifications settings
     *
     * @param \App\Innoclapps\Contracts\Metable $user
     * @param array $data
     *
     * @return void
     */
    public static function updateNotificationSettings(Metable $user, $data) : void
    {
        $user->setMeta('notification-settings', $data);
    }

    /**
     * Check whether the notifications are disabled
     *
     * @return boolean
     */
    public static function notificationsDisabled() : bool
    {
        return count(static::$disabledNotificationsConfig) > 0;
    }

    /**
     * Get the user repository
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    public function getUserRepository()
    {
        return resolve(config('innoclapps.user_repository'));
    }

    /**
     * Check whether there is import in progress
     *
     * @return boolean
     */
    public static function isImportInProgress() : bool
    {
        return static::$importStatus === 'in-progress';
    }

    /**
     * Check whether there is import mapping in progress
     *
     * @return boolean
     */
    public static function isImportMapping() : bool
    {
        return static::$importStatus === 'mapping';
    }

    /**
     * Change the import status
     *
     * @param boolean|string $status
     *
     * @return void
     */
    public static function setImportStatus(bool|string $status = 'mapping') : void
    {
        static::$importStatus = $status;

        if (static::isImportInProgress()) {
            static::disableNotifications();
        } elseif ($status === false) {
            static::enableNotifications();
        }
    }

    /**
     * Get the import status
     *
     * @return boolean|string
     */
    public static function importStatus() : bool|string
    {
        return static::$importStatus;
    }

    /**
     * Check whether the application is installed
     *
     * @return boolean
     */
    public static function isAppInstalled() : bool
    {
        return file_exists(static::installedFileLocation());
    }

    /**
     * Create the installed indicator file
     *
     * @return boolean
     */
    public static function createInstalledFile() : bool
    {
        if (! file_exists(static::installedFileLocation())) {
            $bytes = file_put_contents(
                static::installedFileLocation(),
                'Installation Date:' . date('Y-m-d H:i:s')
            );

            return $bytes !== false ? true : false;
        }

        return false;
    }

    /**
     * Get the installed file locateion
     *
     * @return string
     */
    public static function installedFileLocation() : string
    {
        return storage_path(static::INSTALLED_FILE);
    }

    /**
     * Get the available registered resources names
     *
     * @return array
     */
    public static function getResourcesNames() : array
    {
        return static::registeredResources()->map(fn ($resource) => $resource->name())->all();
    }

    /**
     * Get resources names with fields configured
     *
     * @return array
     */
    public static function resourcesWithFields() : array
    {
        return static::registeredResources()
            ->filter(fn ($resource) => count($resource->fields(request())) > 0)
            ->map(fn ($resource)    => $resource->name())
            ->values()
            ->all();
    }

    /**
     * Get all the registered resources
     *
     * @return \Illuminate\Support\Collection
     */
    public static function registeredResources()
    {
        return is_null(static::$resources) ? collect([]) : static::$resources;
    }

    /**
     * Get the resource class by a given name.
     *
     * @param string $name
     *
     * @return \App\Innoclapps\Resources\Resource|null
     */
    public static function resourceByName(string $name) : ?Resource
    {
        return static::registeredResources()->first(function ($value) use ($name) {
            return $value::name() === $name;
        });
    }

    /**
     * Get the resource class by a given model
     *
     * @param string|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \App\Innoclapps\Resources\Resource|null
     */
    public static function resourceByModel(string|Model $model) : ?Resource
    {
        return static::registeredResources()->first(function ($value) use ($model) {
            return $value::model() === (! is_string($model) ? $model::class : $model);
        });
    }

    /**
     * Get the globally searchable resources
     *
     * @return \Illuminate\Support\Collection
     */
    public static function globallySearchableResources()
    {
        return static::registeredResources()->filter(
            fn ($resource) => $resource::$globallySearchable
        );
    }

    /**
     * Register the given resources.
     *
     * @param array $resources
     *
     * @return void
     */
    public static function resources(array $resources) : void
    {
        static::$resources = static::registeredResources()
            ->merge($resources)->unique(function ($resource) {
                return is_string($resource) ? $resource : $resource::class;
            })->map(function ($resource) {
                return is_string($resource) ? new $resource : $resource;
            });
    }

    /**
     * Register all of the resource classes in the given directory.
     *
     * @param string $directory
     *
     * @return void
     */
    public static function resourcesIn(string $directory) : void
    {
        static::resources(
            Filesystem::listClassFilesOfSubclass(Resource::class, $directory)
        );
    }

    /**
     * Provide data to front-end
     *
     * @param array $data
     *
     * @return void
     */
    public static function provideToScript(array $data) : void
    {
        static::$provideToScript = array_merge(static::$provideToScript, $data);
    }

    /**
     * Get the data provided to script
     *
     * @return array
     */
    public static function getDataProvidedToScript() : array
    {
        return static::$provideToScript;
    }

    /**
     * Register the given script file with the application.
     *
     * @param string $name
     * @param string $path
     *
     * @return void
     */
    public static function script($name, $path)
    {
        static::$scripts[$name] = $path;
    }

    /**
     * Get all of the additional registered scripts
     *
     * @return array
     */
    public static function scripts()
    {
        return static::$scripts;
    }

    /**
     * Register the given CSS file with the application.
     *
     * @param string $name
     * @param string $path
     *
     * @return void
     */
    public static function style($name, $path)
    {
        static::$styles[$name] = $path;
    }

    /**
     * Get all of the additional registered stylesheets
     *
     * @return array
     */
    public static function styles()
    {
        return static::$styles;
    }

    /**
     * Get the application currency
     *
     * @return string
     */
    public static function currency() : string
    {
        return config('innoclapps.currency');
    }

    /**
     * Check whether migration is needed
     *
     * @return boolean
     */
    public static function migrationNeeded() : bool
    {
        return (new Migration(app('migrator')))->needed();
    }

    /**
     * Get the PHP executable path
     *
     * @return string|null
     */
    public static function getPhpExecutablePath()
    {
        $phpFinder = new PhpExecutableFinder;

        try {
            return $phpFinder->find();
        } catch (\Exception $e) {
            return null;
        }
    }
}
