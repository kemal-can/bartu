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

use Illuminate\Translation\FileLoader;
use App\Innoclapps\Contracts\Translation\TranslationLoader;

class LoaderManager extends FileLoader
{
    /**
     * Load the messages for the given locale.
     *
     * @param string $locale
     * @param string $group
     * @param string $namespace
     *
     * @return array
     */
    public function load($locale, $group, $namespace = null) : array
    {
        $originalTranslations = parent::load($locale, $group, $namespace);

        if (! is_null($namespace) && $namespace !== '*') {
            return $originalTranslations;
        }

        // JSON translations are not supported ATM
        if ($group === '*') {
            return $originalTranslations;
        }

        return array_replace_recursive(
            $originalTranslations,
            app(TranslationLoader::class)->loadTranslations($locale, $group)
        );
    }
}
