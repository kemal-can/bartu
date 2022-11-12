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

interface Store
{
    /**
     * Get a specific key from the settings data.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Determine if a key exists in the settings data.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has(string $key) : bool;

    /**
     * Set a specific key to a value in the settings data.
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return static
     */
    public function set(string|array $key, mixed $value = null) : static;

    /**
     * Unset a key in the settings data.
     *
     * @param string $key
     *
     * @return static
     */
    public function forget(string $key) : static;

    /**
     * Flushing all data.
     *
     * @return static
     */
    public function flush() : static;

    /**
     * Get all settings data.
     *
     * @return array
     */
    public function all() : array;

    /**
     * Save any changes done to the settings data.
     *
     * @return static
     */
    public function save() : static;

    /**
     * Check if the data is saved.
     *
     * @return boolean
     */
    public function isSaved() : bool;
}
