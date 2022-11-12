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
use App\Innoclapps\Timeline\Timeline;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Repositories\PinnedTimelineSubjectRepository;

class TimelinePinController extends ApiController
{
    /**
     * Pin the given timelineable to the given resource
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Contracts\Repositories\PinnedTimelineSubjectRepository $repository
     *
     * @return void
     */
    public function store(Request $request, PinnedTimelineSubjectRepository $repository)
    {
        $data = $this->validateRequest($request);

        $repository->pin(...$data);
    }

    /**
     * Unpin the given timelineable to the given resource
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Innoclapps\Contracts\Repositories\PinnedTimelineSubjectRepository $repository
     *
     * @return void
     */
    public function destroy(Request $request, PinnedTimelineSubjectRepository $repository)
    {
        $data = $this->validateRequest($request);

        $repository->unpin(...$data);
    }

    /**
     * Validate the request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function validateRequest($request)
    {
        $data = $request->validate([
            'subject_id'        => 'required|int',
            'subject_type'      => 'required|string',
            'timelineable_id'   => 'required|int',
            'timelineable_type' => 'required|string',
        ]);

        $subject      = Timeline::getPinableSubject($data['subject_type']);
        $timelineable = Timeline::getSubjectAcceptedTimelineable($data['subject_type'], $data['timelineable_type']);

        abort_if(is_null($subject) || is_null($timelineable), 404);

        return [
            $data['subject_id'],
            $subject['subject'],
            $data['timelineable_id'],
            $timelineable['timelineable_type'],
        ];
    }
}
