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

use MediaUploader;
use Illuminate\Http\Request;
use App\Http\Resources\MediaResource;
use App\Http\Controllers\ApiController;
use Plank\Mediable\HandlesMediaUploadExceptions;
use Plank\Mediable\Exceptions\MediaUploadException;
use App\Innoclapps\Contracts\Repositories\PendingMediaRepository;

class PendingMediaController extends ApiController
{
    use HandlesMediaUploadExceptions;

    /**
     * Initialize new PendingMediaController instance
     *
     * @param \App\Innoclapps\Contracts\Repositories\PendingMediaRepository $repository
     */
    public function __construct(protected PendingMediaRepository $repository)
    {
    }

    /**
     * Upload pending media
     *
     * @param string $draftId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($draftId, Request $request)
    {
        try {
            $media = MediaUploader::fromSource($request->file('file'))
                ->toDirectory('pending-attachments')
                ->upload();
            $this->repository->mark($media, $draftId);
        } catch (MediaUploadException $e) {
            $exception = $this->transformMediaUploadException($e);

            return $this->response(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        return $this->response(new MediaResource($media->load('pendingData')), 201);
    }

    /**
     * Destroy pending media
     *
     * @param int $pendingMediaId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($pendingMediaId)
    {
        $this->repository->purge((int) $pendingMediaId);

        return $this->response('', 204);
    }
}
