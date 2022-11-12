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

class NotificationController extends ApiController
{
    /**
     * List current user notifications
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->response(
            $request->user()->notifications()->paginate($request->input('per_page', 15))
        );
    }

    /**
     * Retrieve current user notification
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        return $this->response(
            $request->user()->notifications()->findOrFail($id)
        );
    }

    /**
     * Set all notifications for current user as read
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->user()->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->response('', 204);
    }

    /**
     * Delete current user notification
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $request->user()->notifications()
            ->findOrFail($id)
            ->delete();

        return $this->response('', 204);
    }
}
