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

namespace App\Http\Controllers\Api\Resource;

use MediaUploader;
use App\Http\Resources\MediaResource;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Resources\Mediable;
use Plank\Mediable\HandlesMediaUploadExceptions;
use App\Innoclapps\Resources\Http\ResourceRequest;
use Plank\Mediable\Exceptions\MediaUploadException;
use App\Innoclapps\Contracts\Repositories\MediaRepository;

class MediaController extends ApiController
{
    use HandlesMediaUploadExceptions;

    /**
     * Initialize new MediaController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\MediaRepository $repository
     */
    public function __construct(protected MediaRepository $repository)
    {
    }

    /**
     * Upload media to resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ResourceRequest $request)
    {
        abort_unless($request->resource() instanceof Mediable, 404);

        $this->authorize('update', $record = $request->record());

        try {
            $media = MediaUploader::fromSource($request->file('file'))
                ->toDirectory($record->getMediaDirectory())
                ->upload();
        } catch (MediaUploadException $e) {
            $exception = $this->transformMediaUploadException($e);

            return $this->response(
                ['message' => $exception->getMessage()],
                $exception->getStatusCode()
            );
        }

        $record->attachMedia($media, $record->getMediaTags());

        // Re-query the media with the attached tags
        $media = $this->repository->find($media->id);

        $media->wasRecentlyCreated = true;

        return $this->response(new MediaResource($media), 201);
    }

    /**
     * Delete media from resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ResourceRequest $request)
    {
        abort_unless($request->resource() instanceof Mediable, 404);

        $this->authorize('update', $request->record());
        $this->repository->delete($request->route('media'));

        return $this->response('', 204);
    }
}
