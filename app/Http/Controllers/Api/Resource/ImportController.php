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

use App\Http\Resources\ImportResource;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Resources\Importable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Import\Exceptions\ImportException;
use App\Innoclapps\Contracts\Repositories\ImportRepository;

class ImportController extends ApiController
{
    /**
     * Get the import files for the resource in storage
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\ImportRepository $repository
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(ResourceRequest $request, ImportRepository $repository)
    {
        abort_unless($request->resource() instanceof Importable, 404);

        return ImportResource::collection(
            $repository->with('user')->orderBy('created_at', 'desc')->findWhere([
                'resource_name' => $request->resource()->name(),
            ])
        );
    }

    /**
     * Import resource in storage
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\ImportRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(ResourceRequest $request, ImportRepository $repository)
    {
        if (! app()->runningUnitTests()) {
            @ini_set('memory_limit', '512M');
            @ini_set('max_execution_time', 360);
        }

        abort_unless($request->resource() instanceof Importable, 404);

        $request->validate([
            'mappings'                      => 'required|array',
            'mappings.*.attribute'          => 'nullable|distinct|string',
            'mappings.*.auto_detected'      => 'required|boolean',
            'mappings.*.original'           => 'required|string',
            'mappings.*.skip'               => 'required|boolean',
            'mappings.*.detected_attribute' => 'present',
        ]);

        $import = $repository->find($request->id);

        // Update with the user provided mappings
        $import = $repository->update(
            [
                'data' => array_merge($import->data, [
                    'mappings' => $request->mappings,
                ]),
            ],
            $request->id
        );

        try {
            $request->resource()
                ->importable()
                ->perform($import);

            return $this->response(new ImportResource(
                $repository->with('user')->find($import->getKey())
            ));
        } catch (ImportException $e) {
            return $this->response([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], $e->getCode());
        }
    }

    /**
     * Upload the import file and start mapping
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(ResourceRequest $request)
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $request->validate(['file' => 'required|mimes:csv,txt']);

        $import = $request->resource()->importable()
            ->upload($request->file, $request->user());

        return $this->response(new ImportResource($import));
    }

    /**
     * Delete the given import
     *
     * @param int $id
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\ImportRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ResourceRequest $request, ImportRepository $repository)
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = $repository->find($request->id);

        $this->authorize('delete', $import);

        $repository->delete($request->id);

        return $this->response('', 204);
    }

    /**
     * Download sample import file
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function sample(ResourceRequest $request)
    {
        abort_unless($request->resource() instanceof Importable, 404);

        return $request->resource()->importSample()->download();
    }
}
