<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Workflows\Triggers;

use App\Innoclapps\Fields\Select;
use App\Innoclapps\Workflow\Trigger;
use Illuminate\Support\Facades\Auth;
use App\Workflows\Actions\WebhookAction;
use App\Workflows\Actions\SendEmailAction;
use App\Contracts\Repositories\StageRepository;
use App\Workflows\Actions\CreateActivityAction;
use App\Innoclapps\Contracts\Workflow\ModelTrigger;
use App\Workflows\Actions\ResourcesSendEmailToField;
use App\Workflows\Actions\DeleteAssociatedActivities;
use App\Innoclapps\Contracts\Workflow\FieldChangeTrigger;
use App\Workflows\Actions\MarkAssociatedActivitiesAsComplete;

class DealStageChanged extends Trigger implements FieldChangeTrigger, ModelTrigger
{
    /**
     * Trigger name
     *
     * @return string
     */
    public static function name() : string
    {
        return __('deal.workflows.triggers.stage_changed');
    }

    /**
     * The trigger related model
     *
     * @return string
     */
    public static function model() : string
    {
        return \App\Models\Deal::class;
    }

    /**
     * The field to track changes on
     *
     * @return string
     */
    public static function field() : string
    {
        return 'stage_id';
    }

    /**
     * Provide the change values the user to choose from
     *
     * @return \App\Innoclapps\Fields\Select
     */
    public static function changeField()
    {
        return Select::make(static::field())
            ->labelKey('name')
            ->valueKey('id')
            ->options(function () {
                return resolve(StageRepository::class)->allStagesForOptions(Auth::user());
            });
    }

    /**
     * Trigger available actions
     *
     * @return array
     */
    public function actions() : array
    {
        return [
            new CreateActivityAction,
            (new SendEmailAction)->toResources(ResourcesSendEmailToField::make()->options([
                    'contacts' => [
                        'label'    => __('deal.workflows.actions.fields.email_to_contact'),
                        'resource' => 'contacts',
                    ],
                    'companies' => [
                        'label'    => __('deal.workflows.actions.fields.email_to_company'),
                        'resource' => 'companies',
                    ],
                    'user' => [
                        'label'    => __('deal.workflows.actions.fields.email_to_owner_email'),
                        'resource' => 'users',
                    ],
                    'creator' => [
                        'label'    => __('deal.workflows.actions.fields.email_to_creator_email'),
                        'resource' => 'users',
                    ],
                ])),
            new MarkAssociatedActivitiesAsComplete,
            new DeleteAssociatedActivities,
            new WebhookAction,
        ];
    }
}
