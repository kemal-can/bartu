<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\PredefinedMailTemplate;

class PredefinedMailTemplateTest extends TestCase
{
    public function test_predefined_mail_template_has_user()
    {
        $template = PredefinedMailTemplate::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $template->user);
    }
}
