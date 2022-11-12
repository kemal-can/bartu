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

namespace App\Innoclapps\Timeline;

use App\Innoclapps\Models\PinnedTimelineSubject;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\PinnedTimelineSubjectRepository;

class PinnedTimelineSubjectRepositoryEloquent extends AppRepository implements PinnedTimelineSubjectRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return PinnedTimelineSubject::class;
    }

    /**
     * Pin activity to the given subject
     *
     * @param int $subjectId
     * @param string $subjectType
     * @param int $timelineabeId
     * @param string $timelineableType
     *
     * @return \App\Innoclapps\Models\PinnedTimelineSubject
     */
    public function pin($subjectId, $subjectType, $timelineabeId, $timelineableType)
    {
        return $this->create([
            'subject_id'        => $subjectId,
            'subject_type'      => $subjectType,
            'timelineable_id'   => $timelineabeId,
            'timelineable_type' => $timelineableType,
        ]);
    }

    /**
     * Unpin activity from the given subject
     *
     * @param int $subjectId
     * @param string $subjectType
     * @param int $timelineableId
     * @param string $timelineableType
     *
     * @return \App\Innoclapps\Models\PinnedTimelineSubject
     */
    public function unpin($subjectId, $subjectType, $timelineableId, $timelineableType)
    {
        return $this->deleteWhere([
            'subject_id'        => $subjectId,
            'subject_type'      => $subjectType,
            'timelineable_id'   => $timelineableId,
            'timelineable_type' => $timelineableType,
        ]);
    }
}
