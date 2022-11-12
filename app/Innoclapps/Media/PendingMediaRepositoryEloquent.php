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

namespace App\Innoclapps\Media;

use Illuminate\Support\Arr;
use App\Innoclapps\Models\Media;
use App\Innoclapps\Models\PendingMedia;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use App\Innoclapps\Contracts\Repositories\PendingMediaRepository;

class PendingMediaRepositoryEloquent extends AppRepository implements PendingMediaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return PendingMedia::class;
    }

    /**
     * Get pending media by a given draft id
     *
     * @param string $draftId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDraftId($draftId)
    {
        return $this->with('attachment')->findWhere(['draft_id' => $draftId]);
    }

    /**
     * Get pending media by given token(s)
     *
     * @param array|string $token
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByToken(array|string $token)
    {
        return $this->with('attachment')
            ->whereHas('attachment', function ($query) use ($token) {
                return $query->whereIn('token', Arr::wrap($token));
            })->get();
    }

    /**
     * Mark a given media as pending
     *
     * @param \App\Innoclapps\Models\Media $media
     * @param string $draftId
     *
     * @return \App\Innoclapps\Models\PendingMedia
     */
    public function mark(Media $media, string $draftId) : PendingMedia
    {
        return $this->create(['media_id' => $media->id, 'draft_id' => $draftId]);
    }

    /**
     * Purge mending media by given media
     *
     * @param \App\Innoclapps\Models\PendingMedia|integer $media
     *
     * @return bool
     */
    public function purge(PendingMedia|int $media) : bool
    {
        $pendingMedia = $media instanceof PendingMedia ? $media : $this->find($media);
        app(MediaRepository::class)->delete($pendingMedia->attachment->id);

        return $this->delete($pendingMedia->id);
    }
}
