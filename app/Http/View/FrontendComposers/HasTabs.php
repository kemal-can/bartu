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

namespace App\Http\View\FrontendComposers;

trait HasTabs
{
    /**
     * Registered tabs
     *
     * @var array
     */
    protected static array $tabs = [];

    /**
     * Register new tab
     *
     * @param \App\Http\View\FrontendComposers\Tab $tab
     *
     * @return void
     */
    public static function registerTab(Tab $tab)
    {
        static::$tabs[] = $tab;
    }

    /**
     * Register tabs
     *
     * @param array $tabs
     *
     * @return void
     */
    public static function registerTabs(array $tabs)
    {
        foreach ($tabs as $tab) {
            static::registerTab($tab);
        }
    }
}
