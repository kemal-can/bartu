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

namespace App\Listeners;

use App\Events\DealMovedToStage;
use App\Innoclapps\Facades\ChangeLogger;

class LogDealMovedToStageActivity
{
    /**
     * Log deal stage activity when a stage is changed
     *
     * @param \App\Events\DealMovedToStage $event
     *
     * @return void
     */
    public function handle(DealMovedToStage $event)
    {
        ChangeLogger::generic()->on($event->deal)->withProperties(
            $this->logProperties($event)
        )->log();
    }

    /**
     * Get the log properties
     *
     * @param \App\Events\DealMovedToStage $event
     *
     * @return array
     */
    protected function logProperties(DealMovedToStage $event) : array
    {
        return [
            'icon' => 'Plus',
            'lang' => [
                'key'   => 'deal.timeline.stage.moved',
                'attrs' => [
                    // Name will be replace in the front end from causer_name
                    // saves some database entries duplication
                    'user'     => null,
                    'previous' => $event->previousStage->name,
                    'stage'    => $event->deal->stage->name,
                ],
            ],
        ];
    }
}
