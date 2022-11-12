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

namespace App\Innoclapps\Settings\Stores;

use Illuminate\Support\Facades\Crypt;
use App\Innoclapps\Settings\Utilities\Arr;
use App\Innoclapps\Settings\Contracts\Store;
use Illuminate\Contracts\Foundation\Application;

abstract class AbstractStore implements Store
{
    /**
    * The settings data.
    *
    * @var array
    */
    protected array $data = [];

    /**
    * Whether the store has changed since it was last loaded.
    *
    * @var boolean
    */
    protected bool $unsaved = false;

    /**
    * Whether the settings data are loaded.
    *
    * @var boolean
    */
    protected bool $loaded = false;

    /**
    * AbstractStore constructor.
    *
    * @param \Illuminate\Contracts\Foundation\Application $app
    * @param array $options
    */
    public function __construct(protected Application $app, array $options = [])
    {
        $this->postOptions($options);
    }

    /**
    * Fire the post options to customize the store.
    *
    * @param array $options
    */
    abstract protected function postOptions(array $options);

    /**
    * Read the data from the store.
    *
    * @return array
    */
    abstract protected function read() : array;

    /**
    * Write the data into the store.
    *
    * @param array $data
    *
    * @return void
    */
    abstract protected function write(array $data) : void;

    /**
    * Get a specific key from the settings data.
    *
    * @param string $key
    * @param mixed $default
    *
    * @return mixed
    */
    public function get(string $key, mixed $default = null) : mixed
    {
        $this->checkLoaded();

        $value = Arr::get($this->data, $key, $default);

        if (in_array($key, $this->getEncryptedKeys()) && ! empty($value)) {
            return Crypt::decryptString($value);
        }

        return $value;
    }

    /**
    * Determine if a key exists in the settings data.
    *
    * @param string $key
    *
    * @return boolean
    */
    public function has(string $key) : bool
    {
        $this->checkLoaded();

        return Arr::has($this->data, $key);
    }

    /**
    * Set a specific key to a value in the settings data.
    *
    * @param string|array $key
    * @param mixed $value
    *
    * @return static
    */
    public function set(string|array $key, mixed $value = null) : static
    {
        $this->checkLoaded();
        $this->unsaved = true;

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setValue($k, $v);
            }
        } else {
            $this->setValue($key, $value);
        }

        return $this;
    }

    /**
    * Unset a key in the settings data.
    *
    * @param string $key
    *
    * @return static
    */
    public function forget(string $key) : static
    {
        $this->checkLoaded();

        $this->unsaved = true;

        Arr::forget($this->data, $key);

        return $this;
    }

    /**
    * Flushing all data.
    *
    * @return static
    */
    public function flush() : static
    {
        $this->unsaved = true;
        $this->data    = [];

        return $this;
    }

    /**
    * Get all settings data.
    *
    * @return array
    */
    public function all() : array
    {
        $this->checkLoaded();

        return Arr::map($this->data, function ($value, $key) {
            if (in_array($key, $this->getEncryptedKeys()) && ! empty($value)) {
                return Crypt::decryptString($value);
            }

            return $value;
        });
    }

    /**
    * Save any changes done to the settings data.
    *
    * @return static
    */
    public function save() : static
    {
        if (! $this->isSaved()) {
            $this->write($this->data);
            $this->configureOverrides();
            $this->unsaved = false;
        }

        return $this;
    }

    /**
    * Configure the settings overrides for Laravel configuration.
    *
    * @return void
    */
    public function configureOverrides() : void
    {
        $override = config()->get('settings.override', []);

        foreach (Arr::dot($override) as $configKey => $settingKey) {
            $configKey = $configKey ?: $settingKey;
            $value     = $this->get($settingKey);

            if (! is_null($value)) {
                config()->set([$configKey => $value]);
            }
        }
    }

    /**
    * Check if the data is saved.
    *
    * @return boolean
    */
    public function isSaved() : bool
    {
        return ! $this->unsaved;
    }

    /**
    * Set value to the store.
    *
    * @param string $key
    * @param string $value
    */
    protected function setValue($key, $value) : void
    {
        if (in_array($key, $this->getEncryptedKeys()) && ! empty($value)) {
            $value = Crypt::encryptString($value);
        }

        Arr::set($this->data, $key, $value);
    }

    /**
    * Get the encrypted settings keys.
    *
    * @return array
    */
    protected function getEncryptedKeys() : array
    {
        return config('settings.encrypted', []);
    }

    /**
    * Check if the settings data has been loaded.
    */
    protected function checkLoaded() : void
    {
        if ($this->isLoaded()) {
            return;
        }

        $this->data   = $this->read();
        $this->loaded = true;
    }

    /**
    * Reset the loaded status.
    */
    protected function resetLoaded() : void
    {
        $this->loaded = false;
    }

    /**
    * Check if the data is loaded.
    *
    * @return boolean
    */
    protected function isLoaded() : bool
    {
        return $this->loaded;
    }

    /**
    * TODO: Remove in future, causes issue during update when updating to v1.0.6
    *
    * @deprecated 1.0.6
    */
    public static function setOverrides() : void
    {
    }

    /**
    * TODO: Remove in future, causes issue during update when updating to v1.0.6
    *
    * @deprecated 1.0.6
    */
    protected static function getOverrideValue(string $key) : mixed
    {
    }
}
