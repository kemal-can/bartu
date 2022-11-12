<?php

namespace Tests\Feature\Criteria;

use Tests\TestCase;
use App\Models\PredefinedMailTemplate;
use App\Criteria\PredefinedMailTemplatesForUserCriteria;
use App\Contracts\Repositories\PredefinedMailTemplateRepository;

class PredefinedMailTemplatesForUserCriteriaTest extends TestCase
{
    public function test_it_queries_predefined_mail_templates_for_user()
    {
        $user       = $this->signIn();
        $repository = app(PredefinedMailTemplateRepository::class);
        $repository->pushCriteria(PredefinedMailTemplatesForUserCriteria::class);
        PredefinedMailTemplate::factory()->personal()->create();
        PredefinedMailTemplate::factory()->personal()->for($user)->create();

        $this->assertCount(1, $repository->all());
    }

    public function test_it_includes_the_shared_templates()
    {
        $user       = $this->signIn();
        $repository = app(PredefinedMailTemplateRepository::class);
        $repository->pushCriteria(PredefinedMailTemplatesForUserCriteria::class);
        PredefinedMailTemplate::factory()->shared()->create();
        PredefinedMailTemplate::factory()->personal()->for($user)->create();

        $this->assertCount(2, $repository->all());
    }
}
