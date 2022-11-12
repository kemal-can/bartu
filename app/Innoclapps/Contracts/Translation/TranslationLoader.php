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

namespace App\Innoclapps\Contracts\Translation;

use App\Innoclapps\Translation\DotNotationResult;

interface TranslationLoader
{
    /**
     * Returns all translations for the given locale and group.
     *
     * @param string $locale
     * @param string $group
     *
     * @return array
     */
    public function loadTranslations(string $locale, string $group) : array;

    /**
     * Save the given translations in storage
     *
     * @param string $locale
     * @param string $group
     * @param \App\Innoclapps\Translation\DotNotationResult $translations
     *
     * @return void
     */
    public function saveTranslations(string $locale, string $group, DotNotationResult $translations);

    /**
     * Get the original untouched translations
     *
     * @param string $locale
     *
     * @return \App\Innoclapps\Translation\DotNotationResult
     */
    public function getOriginal(string $locale) : DotNotationResult;
}
