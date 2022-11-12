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
use App\Contracts\Repositories\ActivityRepository;

class ActivityController extends ApiController
{
    /**
     * Download ICS of the given activity
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadICS($id)
    {
        $repository = resolve(ActivityRepository::class);

        $activity = $repository->find($id);

        $this->authorize('view', $activity);

        return response($activity->generateICSInstance()->get(), 200, [
            'Content-Type'        => 'text/calendar',
            'Content-Disposition' => 'attachment; filename=' . $activity->icsFilename() . '.ics',
            'charset'             => 'utf-8',
        ]);
    }
}
