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
use App\Http\Requests\FilterRequest;
use App\Http\Resources\FilterResource;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Contracts\Repositories\FilterRepository;

class FilterController extends ApiController
{
    /**
     * Get filters from storage by identifier for logged in user
     *
     * @param string $identifier
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($identifier, FilterRepository $repository, Request $request)
    {
        $filters = $repository->orderBy('name')->forUser($identifier, $request->user()->id);

        return $this->response(
            FilterResource::collection($filters)
        );
    }

    /**
     * Create new table filter
     *
     * @param \App\Http\Requests\FilterRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(FilterRequest $request, FilterRepository $repository)
    {
        $filter = $repository->create($request->merge(['user_id' => $request->user()->id])->all());

        return $this->response(
            new FilterResource($repository->find($filter->id)),
            201
        );
    }

    /**
     * Update table filter
     *
     * @param \App\Http\Requests\FilterRequest $request
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     * @param mixed $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(FilterRequest $request, FilterRepository $repository, $id)
    {
        $this->authorize('update', $filter = $repository->find($id));

        if ($filter->is_system_default) {
            abort(403, 'Application default filters cannot be updated.');
        } elseif ($filter->is_readonly) {
            abort(403, 'Readonly filters cannot be updated.');
        }

        $filter = $repository->update($request->except(['user_id', 'identifier']), $id);

        return $this->response(
            new FilterResource($repository->find($filter->id))
        );
    }

    /**
     * Delete table filter
     *
     * @param int $id
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, FilterRepository $repository)
    {
        $this->authorize('delete', $filter = $repository->find($id));

        if ($filter->is_system_default) {
            abort(403, 'Application default filters cannot be deleted.');
        } elseif ($filter->is_readonly) {
            abort(403, 'Readonly filters cannot be deleted.');
        }

        $repository->delete($id);

        return $this->response('', 204);
    }

    /**
     * Mark the given filter as default for the given view
     *
     * @param int $id
     * @param string $view
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsDefault($id, $view, Request $request, FilterRepository $repository)
    {
        $filter = $repository->markAsDefault($id, $view, $request->user()->id);

        return $this->response(
            new FilterResource($filter)
        );
    }

    /**
     * Unmark the given filter as default from the given view
     *
     * @param int $id
     * @param string $view
     * @param \App\Innoclapps\Contracts\Repositories\FilterRepository $repository
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unMarkAsDefault($id, $view, Request $request, FilterRepository $repository)
    {
        $filter = $repository->unMarkAsDefault($id, $view, $request->user()->id);

        return $this->response(
            new FilterResource($filter)
        );
    }
}
