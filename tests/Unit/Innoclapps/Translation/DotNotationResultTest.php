<?php

namespace Tests\Unit\Innoclapps\Translation;

use Tests\TestCase;
use JsonSerializable;
use Illuminate\Support\Facades\File;
use App\Innoclapps\Translation\Translation;

class DotNotationResultTest extends TestCase
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

    public function test_it_properly_creates_dot_notation_groups()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $result = Translation::current('en_TEST')->groups();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('locale_group', $result);
        $this->assertCount(2, $result['locale_group']);
        $this->assertArrayHasKey('key', $result['locale_group']);
        $this->assertArrayHasKey('deep.key', $result['locale_group']);
        $this->assertSame('value', $result['locale_group']['key']);
        $this->assertSame('value', $result['locale_group']['deep.key']);
    }

    public function test_it_properly_creates_clean_groups_translation()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $groups = Translation::current('en_TEST')->clean();
        $this->assertIsArray($groups);
        $this->assertArrayHasKey('locale_group', $groups);
        $this->assertCount(2, $groups['locale_group']);
        $this->assertArrayHasKey('key', $groups['locale_group']);
        $this->assertSame('value', $groups['locale_group']['key']);
    }

    public function test_it_can_json_serialize_dot_notation_result()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::copy(base_path('tests/Fixtures/locale_group.php'), lang_path('en_TEST/locale_group.php'));

        $result = Translation::current('en_TEST');

        $this->assertInstanceOf(JsonSerializable::class, $result);

        $groups = $result->jsonSerialize();

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('locale_group', $groups);
        $this->assertCount(2, $groups['locale_group']);
        $this->assertArrayHasKey('key', $groups['locale_group']);
        $this->assertSame('value', $groups['locale_group']['key']);
    }
}
