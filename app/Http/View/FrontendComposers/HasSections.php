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

trait HasSections
{
    /**
     * Registered sections
     *
     * @var array
     */
    protected static array $sections = [];

    /**
     * Register new section
     *
     * @param \App\Http\View\FrontendComposers\Section $section
     *
     * @return void
     */
    public static function registerSection(Section $section)
    {
        static::$sections[] = $section;
    }

    /**
     * Register multiple sections
     *
     * @param array $sections
     *
     * @return void
     */
    public static function registerSections(array $sections)
    {
        foreach ($sections as $section) {
            static::registerSection($section);
        }
    }

    /**
     * Merge the sections options with the given ones section
     *
     * @param array $settings
     *
     * @return array
     */
    public function mergeSections(array $settings) : array
    {
        $settings = collect($settings);

        return collect(static::$sections)->map(function ($section) use ($settings) {
            if ($option = $settings->firstWhere('id', $section->id)) {
                $section->enabled = $option['enabled'];
                $section->order = $option['order'];
            }

            return $section;
        })->sortBy('order')->values()->all();
    }
}
