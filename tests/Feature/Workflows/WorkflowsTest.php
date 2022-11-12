<?php

namespace Tests\Feature\Workflows;

use Tests\TestCase;
use App\Models\Deal;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Activity;
use App\Models\Pipeline;
use Illuminate\Support\Arr;
use App\Innoclapps\Workflow\Workflows;
use App\Workflows\Triggers\DealCreated;
use App\Workflows\Triggers\CompanyCreated;
use App\Workflows\Triggers\ContactCreated;
use App\Workflows\Triggers\DealStageChanged;
use App\Workflows\Actions\CreateActivityAction;
use App\Innoclapps\Contracts\Repositories\WorkflowRepository;

class WorkflowsTest extends TestCase
{
    public function test_company_created_workflow_triggers_create_activity_action()
    {
        $user = $this->signIn();

        $this->createActivityWorkflow(CompanyCreated::class, $user->id);

        $this->postJson('/api/companies', [
            'name' => 'Acme',
        ]);

        $model = Company::first();

        $this->assertCount(1, $model->activities);
        $this->assertArrayHasKey(CreateActivityAction::class, Workflows::$processed);
    }

    public function test_contact_created_workflow_triggers_create_activity_action()
    {
        $user = $this->signIn();

        $this->createActivityWorkflow(ContactCreated::class, $user->id);

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
        ]);

        $model = Contact::first();

        $this->assertCount(1, $model->activities);
        $this->assertArrayHasKey(CreateActivityAction::class, Workflows::$processed);
    }

    public function test_deal_created_workflow_triggers_create_activity_action()
    {
        $user     = $this->signIn();
        $pipeline = Pipeline::factory()->withStages()->create();
        $stage    = $pipeline->stages->first();

        $this->createActivityWorkflow(DealCreated::class, $user->id);

        $this->postJson('/api/deals', [
            'name'        => 'Deal Name',
            'pipeline_id' => $pipeline->id,
            'stage_id'    => $stage->id,
            'user_id'     => $user->id,
        ]);

        $model = Deal::first();

        $this->assertCount(1, $model->activities);
        $this->assertArrayHasKey(CreateActivityAction::class, Workflows::$processed);
    }

    public function test_deal_stage_changed_workflow_triggers_create_activity_action()
    {
        $user = $this->signIn();

        $deal            = Deal::factory()->create();
        $changeStageToId = $deal->pipeline->stages->whereNotIn('id', [$deal->stage_id])->first()->id;

        $this->createActivityWorkflow(DealStageChanged::class, $user->id, [
            'stage_id' => $changeStageToId,
        ]);

        $this->putJson('/api/deals/' . $deal->getKey(), [
            'stage_id' => $changeStageToId,
        ]);

        $this->assertArrayHasKey(CreateActivityAction::class, Workflows::$processed);
    }

    protected function createActivityWorkflow($forTrigger, $userId, $attributes = [])
    {
        $activityAttributes = Activity::factory()->make()->toArray();
        $action             = new CreateActivityAction;
        $fields             = collect($action->fields())->pluck('attribute')->all();

        $attributes = array_merge(Arr::only($activityAttributes, $fields), [
            'activity_title' => $activityAttributes['title'],
            'user_id'        => $userId,
            'due_date'       => 'in_2_days',
        ], $attributes);

        $workflow            = $this->createWorkflow($forTrigger, get_class($action), ['data' => $attributes], $userId);
        $attributes['title'] = $attributes['activity_title'];
        unset($attributes['activity_title']);

        return [
            $workflow,
            $attributes,
        ];
    }

    protected function createWorkflow($trigger, $action, $attributes = [], $userId = 1)
    {
        return resolve(WorkflowRepository::class)->create(
            $this->makeWorkflow(array_merge(
                ['trigger_type' => $trigger, 'action_type' => $action, 'created_by' => $userId],
                $attributes
            ))
        );
    }

    protected function makeWorkflow($attributes = [])
    {
        return array_merge([
            'title'       => 'Title',
            'description' => 'Description',
            'is_active'   => true,
        ], $attributes);
    }
}
