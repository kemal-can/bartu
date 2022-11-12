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
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTimeline
{
    /**
     * Boot the HasTimeline trait
     *
     * @return void
     */
    protected static function bootHasTimeline()
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->pinnedTimelineables->each->delete();
            }
        });
    }

    /**
     * Get the timeline subject key
     *
     * @return string
     */
    public static function getTimelineSubjectKey()
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the subject pinned timelineables models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function pinnedTimelineables() : MorphMany
    {
        return $this->morphMany(PinnedTimelineSubject::class, 'subject');
    }
}
