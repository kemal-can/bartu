<?php

namespace Tests\Fixtures\Workflows;

use App\Models\Contact;
use App\Innoclapps\Workflow\Trigger;
use App\Innoclapps\Contracts\Workflow\EventTrigger;
use App\Innoclapps\Contracts\Workflow\ModelTrigger;

class ContactCreatedTrigger extends Trigger implements ModelTrigger, EventTrigger
{
    /**
     * Trigger name
     *
     * @return string
     */
    public static function name() : string
    {
        return 'Contact created';
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
     * @return string
     */
    public function actions() : array
    {
        return [
            new CreateDealAction,
            new CreateActivityAction,
        ];
    }
}
