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
use App\Innoclapps\Facades\Fields;
use App\Http\Controllers\ApiController;
use App\Http\Requests\CustomFieldRequest;
use App\Http\Resources\CustomFieldResource;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class CustomFieldController extends ApiController
{
    /**
     * Initialize new CustomFieldController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\CustomFieldRepository $repository
     */
    public function __construct(protected CustomFieldRepository $repository)
    {
    }

    /**
     * Get the fields types that can be used as custom fields
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $fields = $this->repository->with('options')
            ->orderBy('created_at')
            ->paginate($request->input('per_page'));

        return $this->response(
            CustomFieldResource::collection($fields)
        );
    }

    /**
     * Create new custom field
     *
     * @param \App\Http\Requests\CustomFieldRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CustomFieldRequest $request)
    {
        $field = $this->repository->create($request->all());

        return $this->response(new CustomFieldResource($field), 201);
    }

    /**
     * Update custom field
     *
     * @param \App\Http\Requests\CustomFieldRequest $request
     * @param mixed $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CustomFieldRequest $request, $id)
    {
        $field = $this->repository->update($request->except(['field_type', 'field_id']), $id);

        return $this->response(new CustomFieldResource($field));
    }

    /**
     * Delete custom field
     *
     * @param int $id
     * @param \App\Innoclapps\Contracts\Repositories\CustomFieldRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->repository->delete($id);

        return $this->response('', 204);
    }
}
