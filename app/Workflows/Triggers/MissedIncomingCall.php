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
use App\Innoclapps\VoIP\Events\IncomingCallMissed;
use App\Innoclapps\Contracts\Workflow\EventTrigger;

class MissedIncomingCall extends Trigger implements EventTrigger
{
    /**
     * Trigger name
     *
     * @return string
     */
    public static function name() : string
    {
        return __('call.workflows.triggers.missed_incoming_call');
    }

    /**
     * The event name the trigger should be triggered
     *
     * @return string
     */
    public static function event() : string
    {
        return IncomingCallMissed::class;
    }

    /**
      * Provide the trigger available actions
      *
      * @return array
      */
    public function actions() : array
    {
        return [
            (new CreateActivityAction)->executing(function ($action) {
                $call = $action->event->call->toArray();
                $action->activity_title .= ' [' . $call['from'] . ']';
                if (! empty($action->note)) {
                    $action->note = $action->note . '<br />============<br />';
                }
                $action->note .= 'From: ' . $call['from'] . '<br />';
                $action->note .= 'To: ' . $call['to'] . '<br />';
                $action->note .= 'Status: ' . $call['status'] . '<br />';
            })->withoutDynamicUsers(),
            new SendEmailAction,
            (new WebhookAction)->executing(function ($action) {
                $action->setPayload($action->event->call->toArray());
            }),
        ];
    }
}
