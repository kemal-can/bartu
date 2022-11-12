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

use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use App\Innoclapps\Settings\Contracts\Store as StoreContract;
use App\Innoclapps\Settings\Contracts\Manager as SettingsManagerContract;

class SettingsManager extends Manager implements SettingsManagerContract
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('settings.default', 'json');
    }

    /**
     * Register a new store.
     *
     * @param string $driver
     * @param array $params
     *
     * @return static
     */
    public function registerStore(string $driver, array $params)
    {
        return $this->extend($driver, function () use ($params) : StoreContract {
            return $this->container->make($params['driver'], [
                'options' => Arr::get($params, 'options', []),
            ]);
        });
    }
}
