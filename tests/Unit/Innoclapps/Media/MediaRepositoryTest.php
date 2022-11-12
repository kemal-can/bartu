<?php

namespace Tests\Unit\Innoclapps\Media;

use Tests\TestCase;
use Tests\Fixtures\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(MediaRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_it_can_find_media_by_token()
    {
        $media = $this->createMedia();

        $this->assertTrue($media->is($this->repository->findByToken($media->token)));
    }

    public function test_it_can_find_media_by_token_or_fail()
    {
        $media = $this->createMedia();

        $this->assertTrue($media->is($this->repository->findByTokenOrFail($media->token)));

        try {
            $this->repository->findByTokenOrFail('fake');
        } catch (\Throwable $e) {
        }

        $this->assertEquals(
            new NotFoundHttpException,
            $e
        );
    }

    public function test_it_can_delete_media_by_tokens()
    {
        $media = $this->createMedia();

        $this->repository->deleteByTokens([$media->token]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_it_can_delete_media_by_token()
    {
        $media = $this->createMedia();

        $this->assertTrue($this->repository->deleteByToken($media->token));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_it_can_purge_mediable_media()
    {
        // With array
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $this->repository->purgeByMediableIds(Event::class, [$event->id]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);

        // With lazy collection
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $this->repository->purgeByMediableIds(Event::class, new LazyCollection([$event->id]));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);

        // With regular collection
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $this->repository->purgeByMediableIds(Event::class, new Collection([$event->id]));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);
    }

    public function test_it_does_not_make_query_when_pruning_if_the_mediable_ids_count_is_zero()
    {
        $this->assertFalse($this->repository->purgeByMediableIds(Event::class, []));
    }

    protected function createMedia()
    {
        return $this->repository->unguarded(function ($repository) {
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
