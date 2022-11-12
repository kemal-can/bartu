<?php

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\SampleMailTemplate;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Facades\MailableTemplates;
use App\Innoclapps\Contracts\Repositories\MailableRepository;

class MailableControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_mailable_templates_endpoints()
    {
        $this->getJson('/api/mailables')->assertUnauthorized();
        $this->getJson('/api/mailables/en/locale')->assertUnauthorized();
        $this->getJson('/api/mailables/FAKE_ID')->assertUnauthorized();
        $this->putJson('/api/mailables/FAKE_ID')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_mailable_template_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/mailables')->assertForbidden();
        $this->getJson('/api/mailables/en/locale')->assertForbidden();
        $this->getJson('/api/mailables/FAKE_ID')->assertForbidden();
        $this->putJson('/api/mailables/FAKE_ID')->assertForbidden();
    }

    public function test_user_can_retrieve_all_mailable_templates()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $this->getJson('/api/mailables')
            ->assertJsonCount(count(Translation::availableLocales()))
            ->assertJsonPath('0.name', SampleMailTemplate::name());
    }

    public function test_user_can_retrieve_mailable_templates_by_locale()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $this->getJson('/api/mailables/en/locale')->assertJsonCount(1)->assertJsonPath('0.name', SampleMailTemplate::name());
    }

    public function test_user_can_retrieve_mailable_template()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $template = app(MailableRepository::class)->columns('id')->forMailable(SampleMailTemplate::class, 'en');

        $this->getJson('/api/mailables/' . $template->id)->assertJson(['name' => SampleMailTemplate::name()]);
    }

    public function test_user_can_update_mailable_template()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $template = app(MailableRepository::class)->forMailable(SampleMailTemplate::class, 'en');

        $this->putJson('/api/mailables/' . $template->id, $data = [
            'subject'       => 'Changed Subject',
            'html_template' => 'Changed HTML Template',
            'text_template' => 'Changed Text Template',
        ])->assertJson($data);
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
