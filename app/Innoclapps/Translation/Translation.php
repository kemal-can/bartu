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

namespace App\Innoclapps\Translation;

use App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class Translation
{
    /**
     * Generate JSON language file
     *
     * @return void
     */
    public static function generateJsonLanguageFile() : void
    {
        (new JsonGenerator())->generateTo(
            config('innoclapps.lang.json')
        );
    }

    /**
     * Get the available locales
     *
     * @return array
     */
    public static function availableLocales() : array
    {
        return once(function () {
            return collect(File::directories(App::langPath()))
                ->map(fn ($locale)    => basename($locale))
                ->reject(fn ($locale) => $locale === 'vendor')
                ->unique()
                ->values()->all();
        });
    }

    /**
     * Check whether the given locale exist
     *
     * @param string $locale
     *
     * @return boolean
     */
    public static function localeExist(string $locale) : bool
    {
        return in_array($locale, static::availableLocales());
    }

    /**
     * Create new locale
     *
     * @param string $name
     *
     * @return boolean
     */
    public static function createNewLocale(string $name) : bool
    {
        $sourceDir = App::langPath('en');
        $targetDir = App::langPath($name);

        return tap(File::copyDirectory($sourceDir, $targetDir), function () {
            // availableLocales is using the once() function
            \Spatie\Once\Cache::getInstance()->flush();
            static::generateJsonLanguageFile();
        });
    }

    /**
     * Retrieve the given locale groups files
     *
     * @param string $locale
     *
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public static function retrieveGroupFiles(string $locale)
    {
        $path = App::langPath() . DIRECTORY_SEPARATOR . $locale;

        if (! is_dir($path)) {
            return [];
        }

        return File::files($path);
    }

    /**
     * Get the given locale groups translations
     *
     * @param string $locale
     *
     * @return array
     */
    public static function getGroupsTranslations(string $locale) : array
    {
        $translations = [];

        foreach (static::retrieveGroupFiles($locale) as $fileInfo) {
            $groupName                = $fileInfo->getFilenameWithoutExtension();
            $translations[$groupName] = Lang::getLoader()->load($locale, $groupName);
        }

        return $translations;
    }

    /**
     * Get the current original translations for a given locale
     *
     * @param string $locale
     *
     * @return \App\Innoclapps\Translation\DotNotationResult
     */
    public static function current(string $locale) : DotNotationResult
    {
        return new DotNotationResult(
            static::getGroupsTranslations($locale)
        );
    }
}
