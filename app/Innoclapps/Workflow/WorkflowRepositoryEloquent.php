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

use App\Innoclapps\Models\Workflow;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\WorkflowRepository;

class WorkflowRepositoryEloquent extends AppRepository implements WorkflowRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Workflow::class;
    }
}
