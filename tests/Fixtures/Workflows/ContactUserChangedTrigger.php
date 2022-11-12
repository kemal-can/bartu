<?php

namespace Tests\Fixtures\Workflows;

use App\Models\Contact;
use App\Innoclapps\Fields\Select;
use App\Innoclapps\Workflow\Trigger;
use App\Innoclapps\Contracts\Workflow\ModelTrigger;
use App\Innoclapps\Contracts\Workflow\FieldChangeTrigger;

class ContactUserChangedTrigger extends Trigger implements FieldChangeTrigger, ModelTrigger
{
    /**
     * Trigger name
     *
     * @return string
     */
    public static function name() : string
    {
        return 'Contact user changed';
    }

    /**
     * The trigger related model
     *
     * @return string
     */
    public static function model() : string
    {
        return Contact::class;
    }

    /**
     * The field to track changes on
     *
     * @return string
     */
    public static function field() : string
    {
        return 'user_id';
    }

    /**
     * Provide the change values the user to choose from
     *
     * @return \App\Innoclapps\Fields\Select
     */
    public static function changeField()
    {
        return Select::make('owner')
            ->labelKey('name')
            ->valueKey('id')
            ->options(function () {
                return []; // not used atm
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
            new CreateDealAction,
            new CreateActivityAction,
        ];
    }
}
