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

namespace App\Innoclapps\Settings\Contracts;

use Closure;

interface Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers();

    /**
     * Get a driver instance.
     *
     * @param string|null $driver
     *
     * @return \App\Innoclapps\Settings\Contracts\Store
     */
    public function driver($driver = null);

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @param \Closure $callback
     *
     * @return static
     */
    public function extend($driver, Closure $callback);

    /**
     * Register a new store.
     *
     * @param string $driver
     * @param array $params
     *
     * @return static
     */
    public function registerStore(string $driver, array $params);
}
