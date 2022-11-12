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

class SettingsMenu
{
    /**
     * @var array
     */
    protected static array $items = [];

    /**
     * Register new settings menu item
     *
     * @param \App\Innoclapps\Settings\SettingsMenuItem $item
     * @param string $id
     *
     * @return void
     */
    public static function register(SettingsMenuItem $item, string $id) : void
    {
        static::$items[$id] = $item->setId($id);
    }

    /**
     * Find menu item by the given id
     *
     * @param string $id
     *
     * @return \App\Innoclapps\Settings\SettingsMenuItem|null
     */
    public static function find(string $id) : ?SettingsMenuItem
    {
        return collect(static::$items)->first(fn ($item) => $item->getId() === $id);
    }

    /**
     * Get all of the registered settings menu items
     *
     * @return array
     */
    public static function all() : array
    {
        return collect(static::$items)->sortBy('order')->values()->all();
    }
}
