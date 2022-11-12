<?php

namespace Tests\Unit\Innoclapps\Translation\Loaders;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use App\Innoclapps\Translation\DotNotationResult;
use App\Innoclapps\Contracts\Translation\TranslationLoader;

class OverrideFileLoaderTest extends TestCase
{
    protected function tearDown() : void
    {
        foreach (['en_TEST', '.custom/en_TEST', '.custom'] as $folder) {
            $path = lang_path($folder);

            if (is_dir($path)) {
                File::cleanDirectory($path);
                rmdir($path);
            }
        }

        parent::tearDown();
    }

    public function test_it_can_load_the_overriden_translations()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $override = app(TranslationLoader::class);

        $override->saveTranslations('en_TEST', 'locale_group', new DotNotationResult([
            'key'  => 'changed',
            'deep' => [
                'key' => 'changed',
            ],
        ]));

        $translations = $override->loadTranslations('en_TEST', 'locale_group');

        $this->assertIsArray($translations);
        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('key', $translations);
        $this->assertArrayHasKey('deep', $translations);
        $this->assertSame('changed', $translations['key']);
        $this->assertSame('changed', $translations['deep']['key']);
    }

    public function test_it_can_save_custom_translations()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $override = app(TranslationLoader::class);

        $override->saveTranslations('en_TEST', 'locale_group', new DotNotationResult([
            'key'  => 'changed',
            'deep' => [
                'key' => 'changed',
            ],
        ]));

        $this->assertDirectoryExists(lang_path('.custom/en_TEST'));
        $this->assertFileExists(lang_path('.custom/en_TEST/locale_group.php'));
    }

    public function test_it_can_return_the_original_translations()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $override = app(TranslationLoader::class);
        $override->saveTranslations('en_TEST', 'locale_group', new DotNotationResult([
            'key'  => 'changed',
            'deep' => [
                'key' => 'changed',
            ],
        ]));

        $groups = $override->getOriginal('en_TEST')->clean();

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('locale_group', $groups);
        $this->assertCount(2, $groups['locale_group']);
        $this->assertArrayHasKey('key', $groups['locale_group']);
        $this->assertSame('value', $groups['locale_group']['key']);
    }
}
