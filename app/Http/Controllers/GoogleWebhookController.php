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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Repositories\SynchronizationRepository;

class GoogleWebhookController extends Controller
{
    /**
     *  Handle the webhook request
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\SynchronizationRepository $repository
     *
     * @return void
     */
    public function handle(Request $request, SynchronizationRepository $repository)
    {
        if ($request->header('x-goog-resource-state') !== 'exists') {
            return;
        }

        $synchronization = $repository->findWhere([
            'id'          => $request->header('x-goog-channel-id'),
            'resource_id' => $request->header('x-goog-resource-id'),
        ])->first();

        abort_unless($synchronization, 404);

        $synchronization->ping();
    }
}
