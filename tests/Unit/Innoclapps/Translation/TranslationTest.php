<?php

namespace Tests\Unit\Innoclapps\Translation;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Translation\DotNotationResult;

class TranslationTest extends TestCase
{
    protected function tearDown() : void
    {
        if (is_dir(lang_path('en_TEST'))) {
            File::cleanDirectory(lang_path('en_TEST'));
            rmdir(lang_path('en_TEST'));
        }

        parent::tearDown();
    }

    public function test_it_can_generate_json_language_file()
    {
        $path = config('innoclapps.lang.json');

        if (file_exists($path) && ! unlink($path)) {
            $this->markTestSkipped('Failed to remove the language file.');
        }

        Translation::generateJsonLanguageFile();

        $this->assertFileExists($path);
    }

    public function test_it_can_determine_whether_locale_exists()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        $this->assertTrue(Translation::localeExist('en_TEST'));
    }

    public function test_it_can_retrieve_group_files()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $groups = Translation::retrieveGroupFiles('en_TEST');

        $this->assertNotEmpty($groups);
        $this->assertCount(1, $groups);
    }

    public function test_it_can_create_new_locale()
    {
        Translation::createNewLocale('en_TEST');

        $this->assertDirectoryExists(lang_path('en_TEST'));
        $this->assertDirectoryIsReadable(lang_path('en_TEST'));
        $this->assertCount(count(Translation::retrieveGroupFiles('en')), Translation::retrieveGroupFiles('en_TEST'));
    }

    public function test_it_can_retrieve_groups_translations()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $groups = Translation::getGroupsTranslations('en_TEST');

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('locale_group', $groups);
        $this->assertCount(2, $groups['locale_group']);
        $this->assertArrayHasKey('key', $groups['locale_group']);
        $this->assertSame('value', $groups['locale_group']['key']);
    }

    public function test_it_can_retrieve_original_locale_transalations_in_dot_notation_result()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $this->assertInstanceOf(DotNotationResult::class, Translation::current('en_TEST'));
    }

    public function test_it_does_not_show_error_when_retrieving_group_files_and_locales_does_not_exists()
    {
        $groups = Translation::retrieveGroupFiles('en_TEST');

        $this->assertIsArray($groups);
        $this->assertEmpty($groups);
    }
}
