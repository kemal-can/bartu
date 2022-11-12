<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use App\Models\WebForm;
use App\Innoclapps\Fields\User;
use Database\Seeders\SourceSeeder;

class WebFormControllerTest extends TestCase
{
    protected function tearDown() : void
    {
        User::setAssigneer(null);
        parent::tearDown();
    }

    public function test_web_form_can_be_submitted()
    {
        $this->seed(SourceSeeder::class);

        $form = WebForm::factory()
            ->addFieldSection('first_name', 'contacts', ['requestAttribute' => 'first_name', 'isRequired' => true])
            ->addFieldSection('last_name', 'contacts', ['requestAttribute' => 'last_name'])
            ->addFieldSection('email', 'contacts', [ 'requestAttribute' => 'email'])
            ->create();

        $this->post($form->publicUrl, [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
        ])->assertNoContent();
    }

    public function test_web_form_required_fields_are_validated()
    {
        $form = WebForm::factory()
            ->addFieldSection('first_name', 'contacts', ['requestAttribute' => 'first_name', 'isRequired' => true])
            ->addFieldSection('last_name', 'contacts', ['label' => 'contact_last_name', 'requestAttribute' => 'last_name'])
            ->addFieldSection('email', 'contacts', ['label' => 'contact_email', 'isRequired' => true, 'requestAttribute' => 'email'])
            ->withSubmitButtonSection()
            ->create();

        $this->post($form->publicUrl)
            ->assertSessionHasErrors(['first_name', 'email'])
            ->assertSessionDoesntHaveErrors('last_name');
    }
}
