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

use App\Innoclapps\Contracts\Repositories\PendingMediaRepository;

class PruneStaleMediaAttachments
{
    /**
     * Prune the stale attached media from the system.
     *
     * @return void
     */
    public function __invoke()
    {
        $repository = resolve(PendingMediaRepository::class);

        $repository->orderBy('id', 'desc')
            ->with('attachment')
            ->findWhere([
            ['created_at', '<=', now()->subDays(1)],
        ])->each(function ($media) use ($repository) {
            $repository->purge($media);
        });
    }
}
