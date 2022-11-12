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

namespace App\Http\Controllers\Api\Workflow;

use App\Innoclapps\Workflow\Workflows;
use App\Http\Controllers\ApiController;

class WorkflowTriggers extends ApiController
{
    /**
     * Get the available triggers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        return $this->response(Workflows::triggersInstance());
    }
}
