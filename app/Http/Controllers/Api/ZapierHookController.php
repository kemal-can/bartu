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

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Repositories\ZapierHookRepository;

class ZapierHookController extends ApiController
{
    /**
     * Initialize new ZapierHookController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\ZapierHookRepository $repository
     */
    public function __construct(protected ZapierHookRepository $repository)
    {
    }

    /**
     * Subscribe to a hook
     *
     * @param string $resourceName
     * @param string $action
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($resourceName, $action, Request $request)
    {
        return $this->response($this->repository->create([
            'hook'          => $request->targetUrl,
            'resource_name' => $resourceName,
            'action'        => $action,
            'user_id'       => $request->user()->id,
            // Needs further testing, previously the zapId was only numeric
            // but now includes subscriptions:zapId
            'zap_id' => str_contains($request->zapId, 'subscription:') ?
                explode('subscription:', $request->zapId)[1] :
                $request->zapId,
            'data' => $request->data,
        ]), 201);
    }

    /**
     * Unsubscribe from hook
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $this->repository->deleteForUser($id, $request->user()->id);

        return $this->response('', 204);
    }
}
