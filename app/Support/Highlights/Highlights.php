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

namespace App\Support\Highlights;

class Highlights
{
    /**
     * @var array
     */
    protected static array $highlights = [];

    /**
     * Application default highlights
     *
     * @var array
     */
    protected static $defaults = [
        OpenDeals::class,
        TodaysActivities::class,
    ];

    /**
     * Get all the highlights
     *
     * @return array
     */
    public static function get() : array
    {
        return array_merge(static::$highlights, array_map(fn ($class) => new $class, static::$defaults));
    }

    /**
     * Register new highlight
     *
     * @param \App\Support\Highlights\Highlight $highlight
     *
     * @return static
     */
    public static function register(Highlight $highlight) : static
    {
        static::$highlights[] = $highlight;

        return new static;
    }
}
