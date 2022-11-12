<?php

namespace Tests\Browser;

use App\Models\WebForm;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Database\Seeders\SourceSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group dusk
 */
class WebFormViewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_web_form_can_be_rendered()
    {
        $form = WebForm::factory()
            ->withIntroductionSection([
                'title'   => 'Introduction Title',
                'message' => 'Introduction Message',
            ])
            ->addFieldSection('first_name', 'contacts', ['label' => 'contact_first_name'])
            ->addFieldSection('last_name', 'contacts', ['label' => 'contact_last_name'])
            ->addFieldSection('email', 'contacts', ['label' => 'contact_email'])
            ->addFieldSection('phones', 'contacts', ['label' => 'contact_phone'])
            ->addFileSection('contacts', ['label' => 'contacts_file'])
            ->addFieldSection('name', 'deals', ['label' => 'deal_name'])
            ->addFieldSection('amount', 'deals', ['label' => 'deal_amount'])
            ->addFieldSection('expected_close_date', 'deals', ['label' => 'deal_expected_close_date'])
            ->addFileSection('deals', ['label' => 'deals_file'])
            ->addFieldSection('name', 'companies', ['label' => 'company_name'])
            ->addFieldSection('email', 'companies', ['label' => 'company_email'])
            ->addFieldSection('domain', 'companies', ['label' => 'company_domain'])
            ->addFileSection('companies', ['label' => 'companies_file'])
            ->withSubmitButtonSection([
                'text'                          => 'Submit this form',
                'privacyPolicyAcceptIsRequired' => true,
                'privacyPolicyUrl'              => 'https://bartucrm.com/privacy-policy',
            ])->create();

        $this->browse(function (Browser $browser) use ($form) {
            $browser->visitRoute('webform.view', $form->uuid)
                ->with('@web-form', function ($form) {
                    $form->assertTitle('Introduction Title')
                        ->assertSee('Introduction Title')
                        ->assertSee('Introduction Message')
                        ->assertSee('contact_first_name')
                        ->assertSee('contact_last_name')
                        ->assertSee('contact_email')
                        ->assertSee('contact_phone')
                        ->assertSee('contacts_file')
                        ->assertSee('deal_name')
                        ->assertSee('deal_amount')
                        ->assertSee('deal_expected_close_date')
                        ->assertSee('deals_file')
                        ->assertSee('company_name')
                        ->assertSee('company_email')
                        ->assertSee('company_domain')
                        ->assertSee('companies_file')
                        ->assertNotChecked('#acceptPrivacyPolicy')
                        ->assertSeeLink('Privacy Policy')
                        ->assertSee('Submit this form');
                });
        });
    }

    public function test_it_shows_the_specified_submit_message_after_form_submission()
    {
        $this->seed(SourceSeeder::class);

        $form = WebForm::factory()
            ->addFieldSection('email', 'contacts', ['requestAttribute' => 'email'])
            ->withSubmitButtonSection([
                'text' => 'Submit this form',
            ])
            ->mergeSubmitData([
                'action'        => 'message',
                'success_title' => 'Form submitted.',
            ])
            ->create();

        $this->browse(function (Browser $browser) use ($form) {
            $browser->visitRoute('webform.view', $form->uuid)
                ->type('#email', 'email@example.com')
                ->pressAndWaitFor('#submitButton')
                ->assertSee('Form submitted.');
        });
    }
}
