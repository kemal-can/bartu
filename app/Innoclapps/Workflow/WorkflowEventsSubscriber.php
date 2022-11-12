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

namespace App\Innoclapps\Workflow;

use App\Innoclapps\Contracts\Repositories\WorkflowRepository;

class WorkflowEventsSubscriber
{
    /**
     * Create new WorkflowEventsSubscriber instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\WorkflowRepository $repository
     */
    public function __construct(protected WorkflowRepository $repository)
    {
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        foreach (Workflows::$eventOnlyListeners as $data) {
            $events->listen($data['event'], function ($event) use ($data) {
                $workflows = $this->repository->findWhere(['trigger_type' => $data['trigger']]);

                foreach ($workflows as $workflow) {
                    Workflows::process($workflow, ['event' => $event]);
                }
            });
        }
    }
}
