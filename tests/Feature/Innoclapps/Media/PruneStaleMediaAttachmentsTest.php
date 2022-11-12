<?php

namespace Tests\Feature\Innoclapps\Media;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Innoclapps\Media\PruneStaleMediaAttachments;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use App\Innoclapps\Contracts\Repositories\PendingMediaRepository;

class PruneStaleMediaAttachmentsTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(PendingMediaRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_prunes_stale_media_attachments()
    {
        Carbon::setTestNow(now()->subDay(1)->startOfDay());
        $media = $this->createMedia();

        $pendingMedia = $this->repository->mark($media, 'draft-id');

        Carbon::setTestNow(null);

        (new PruneStaleMediaAttachments)();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('pending_media_attachments', ['id' => $pendingMedia->id]);
    }

    protected function createMedia()
    {
        return app(MediaRepository::class)->unguarded(function ($repository) {
            return $repository->create([
                'disk'           => 'local',
                'directory'      => 'media',
                'filename'       => 'filename',
                'extension'      => 'jpg',
                'mime_type'      => 'image/jpg',
                'size'           => 200,
                'aggregate_type' => 'image',
            ]);
        });
    }
}
