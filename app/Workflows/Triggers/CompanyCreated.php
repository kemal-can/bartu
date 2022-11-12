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

use App\Innoclapps\Workflow\Trigger;
use App\Workflows\Actions\WebhookAction;
use App\Workflows\Actions\SendEmailAction;
use App\Workflows\Actions\CreateActivityAction;
use App\Innoclapps\Contracts\Workflow\EventTrigger;
use App\Innoclapps\Contracts\Workflow\ModelTrigger;
use App\Workflows\Actions\ResourcesSendEmailToField;

class CompanyCreated extends Trigger implements ModelTrigger, EventTrigger
{
    /**
     * Trigger name
     *
     * @return string
     */
    public static function name() : string
    {
        return __('company.workflows.triggers.created');
    }

    /**
     * The trigger related model
     *
     * @return string
     */
    public static function model() : string
    {
        return \App\Models\Company::class;
    }

    /**
     * The model event trigger
     *
     * @return string
     */
    public static function event() : string
    {
        return 'created';
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
                    'self' => [
                        'label'    => __('company.workflows.actions.fields.email_to_company'),
                        'resource' => 'companies',
                    ],
                    'user' => [
                        'label'    => __('company.workflows.actions.fields.email_to_owner_email'),
                        'resource' => 'users',
                    ],
                    'creator' => [
                        'label'    => __('company.workflows.actions.fields.email_to_creator_email'),
                        'resource' => 'users',
                    ],
                    'contacts' => [
                        'label'    => __('company.workflows.actions.fields.email_to_contact'),
                        'resource' => 'contacts',
                    ],
                ])),
            new WebhookAction,
        ];
    }
}
