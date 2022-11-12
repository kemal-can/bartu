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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ActivityResource;
use App\Contracts\Repositories\ActivityRepository;

class ActivityStateController extends ApiController
{
    /**
     * Initialize new ActivityStateController instance.
     *
     * @param \App\Contracts\Repositories\ActivityRepository $repository
     */
    public function __construct(protected ActivityRepository $repository)
    {
    }

    /**
     * Mark activity as complete
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete($id)
    {
        $this->authorize('changeState', $activity = $this->repository->find($id));

        $activity = $this->repository->complete($activity);

        return $this->response(
            new ActivityResource($activity->resource()->displayQuery()->find($activity->id))
        );
    }

    /**
     * Mark activity as incomplete
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomplete($id)
    {
        $this->authorize('changeState', $activity = $this->repository->find($id));

        $activity = $this->repository->incomplete($activity);

        return $this->response(
            new ActivityResource($activity->resource()->displayQuery()->find($activity->id))
        );
    }
}
