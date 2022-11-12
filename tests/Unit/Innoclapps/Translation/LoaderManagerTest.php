<?php

namespace Tests\Unit\Innoclapps\Translation;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use App\Innoclapps\Translation\LoaderManager;
use App\Innoclapps\Translation\DotNotationResult;
use App\Innoclapps\Contracts\Translation\TranslationLoader;

class LoaderManagerTest extends TestCase
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

    public function test_it_uses_the_loader_manager()
    {
        $this->assertInstanceOf(LoaderManager::class, app('translation.loader'));
    }

    public function test_it_can_loader_locale_translation_group()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $manager            = app('translation.loader');
        $groupsTranslations = $manager->load('en_TEST', 'locale_group');

        $this->assertIsArray($groupsTranslations);
        $this->assertCount(2, $groupsTranslations);
        $this->assertArrayHasKey('key', $groupsTranslations);
        $this->assertArrayHasKey('deep', $groupsTranslations);
    }

    public function test_it_merges_the_custom_translations()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $override = app(TranslationLoader::class);
        $override->saveTranslations('en_TEST', 'locale_group', new DotNotationResult([
            'key'  => 'changed',
            'deep' => [
                'key' => 'changed',
            ],
            'new' => 'value',
        ]));

        $manager            = app('translation.loader');
        $groupsTranslations = $manager->load('en_TEST', 'locale_group');

        $this->assertIsArray($groupsTranslations);
        $this->assertCount(3, $groupsTranslations);
        $this->assertArrayHasKey('key', $groupsTranslations);
        $this->assertArrayHasKey('deep', $groupsTranslations);
        $this->assertArrayHasKey('new', $groupsTranslations);
        $this->assertSame('changed', $groupsTranslations['key']);
        $this->assertSame('changed', $groupsTranslations['deep']['key']);
        $this->assertSame('value', $groupsTranslations['new']);
    }
}
