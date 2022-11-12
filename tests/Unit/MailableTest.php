<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\SampleMailTemplate;
use App\Innoclapps\Facades\MailableTemplates;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use App\Innoclapps\Contracts\Repositories\MailableRepository;

class MailableTest extends TestCase
{
    public function test_mailable_template_is_seeded_when_new_mailable_exist()
    {
        RefreshDatabaseState::$lazilyRefreshed = true;
        $this->baseRefreshDatabase();

        MailableTemplates::dontDiscover();
        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $this->assertDatabaseHas('mailable_templates', [
            'name'          => SampleMailTemplate::name(),
            'subject'       => SampleMailTemplate::defaultSubject(),
            'html_template' => SampleMailTemplate::defaultHtmlTemplate(),
            'text_template' => SampleMailTemplate::defaultTextMessage(),
            'mailable'      => SampleMailTemplate::class,
            'locale'        => 'en',
        ]);
    }

    public function test_mailable_templates_are_seeded_when_new_locale_exist()
    {
        RefreshDatabaseState::$lazilyRefreshed = true;
        $this->baseRefreshDatabase();

        $repository = resolve(MailableRepository::class);

        File::ensureDirectoryExists(lang_path('en_TEST'));

        MailableTemplates::seedIfRequired();

        $total = count(MailableTemplates::get());
        $this->assertCount($total, $repository->forLocale('en_TEST'));
    }

    public function test_mailable_templates_are_seeded_for_all_locales()
    {
        RefreshDatabaseState::$lazilyRefreshed = true;
        $this->baseRefreshDatabase();

        $repository = resolve(MailableRepository::class);

        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::ensureDirectoryExists(lang_path('fr_TEST'));

        MailableTemplates::seedIfRequired();

        $total = count(MailableTemplates::get());
        $this->assertCount($total, $repository->forLocale('en_TEST'));
        $this->assertCount($total, $repository->forLocale('fr_TEST'));
    }

    protected function tearDown() : void
    {
        foreach (['en_TEST', 'fr_TEST'] as $locale) {
            $path = lang_path($locale);

            if (is_dir($path)) {
                File::cleanDirectory($path);
                rmdir($path);
            }
        }

        parent::tearDown();
    }
}
