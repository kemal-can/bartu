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

namespace App\Innoclapps\Translation\Loaders;

use Illuminate\Support\Facades\File;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Translation\DotNotationResult;
use App\Innoclapps\Contracts\Translation\TranslationLoader;

class OverrideFileLoader implements TranslationLoader
{
    /**
     * The custom translator override path
     *
     * @var string
     */
    protected string $overridePath;

    /**
     * Create new OverrideFileLoader instance
     *
     * @param array $config
     */
    public function __construct(protected array $config)
    {
        $this->overridePath = $config['path'];
    }

    /**
     * Returns all translations for the given locale and group.
     *
     * @param string $locale
     * @param string $group
     *
     * @return array
     */
    public function loadTranslations(string $locale, string $group) : array
    {
        $groupPath = $this->overridePath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $group . '.php';

        if (file_exists($groupPath)) {
            $translations = include($groupPath);
        }

        return $translations ?? [];
    }

    /**
     * Save the given translations in storage
     *
     * @param string $locale
     * @param string $group
     * @param \App\Innoclapps\Translation\DotNotationResult $translations
     *
     * @return void
     */
    public function saveTranslations(string $locale, string $group, DotNotationResult $translations)
    {
        File::ensureDirectoryExists(
            $localePath = $this->overridePath . DIRECTORY_SEPARATOR . $locale
        );

        File::put(
            $localePath . DIRECTORY_SEPARATOR . $group . '.php',
            "<?php\n\nreturn " . var_export($translations->clean(), true) . ';' . \PHP_EOL
        );

        Translation::generateJsonLanguageFile();
    }

    /**
     * Get the original unmodified translations
     *
     * @param string $locale
     *
     * @return \App\Innoclapps\Translation\DotNotationResult
     */
    public function getOriginal(string $locale) : DotNotationResult
    {
        if (! is_dir($this->config['lang_path'] . DIRECTORY_SEPARATOR . $locale)) {
            $locale = $this->config['fallback_locale'];
        }

        $files = File::files($this->config['lang_path'] . DIRECTORY_SEPARATOR . $locale);

        return new DotNotationResult(collect($files)->mapWithKeys(function ($fileInfo) {
            return [$fileInfo->getFilenameWithoutExtension() => include $fileInfo->getPathname()];
        })->all());
    }
}
