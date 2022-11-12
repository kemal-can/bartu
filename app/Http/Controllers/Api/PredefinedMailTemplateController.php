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

use App\Http\Controllers\ApiController;
use App\Http\Requests\PredefinedMailTemplateRequest;
use App\Http\Resources\PredefinedMailTemplateResource;
use App\Criteria\PredefinedMailTemplatesForUserCriteria;
use App\Contracts\Repositories\PredefinedMailTemplateRepository;

class PredefinedMailTemplateController extends ApiController
{
    /**
     * PredefinedMailTemplateController constructor.
     *
     * @param \App\Innoclapps\Contracts\Repositories\PredefinedMailTemplateRepository $repository
     */
    public function __construct(protected PredefinedMailTemplateRepository $repository)
    {
    }

    /**
     * Display a listing of the mail templates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $templates = PredefinedMailTemplateResource::collection(
            $this->repository->with('user')
                ->pushCriteria(PredefinedMailTemplatesForUserCriteria::class)->all()
        );

        return $this->response($templates);
    }

    /**
     * Display the specified mail template.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $template = $this->repository->with('user')->find($id);

        $this->authorize('view', $template);

        return $this->response(new PredefinedMailTemplateResource($template));
    }

    /**
     * Store a newly created mail template in storage
     *
     * @param \App\Http\Requests\PredefinedMailTemplateRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PredefinedMailTemplateRequest $request)
    {
        $template = $this->repository->create($request->merge(['user_id' => $request->user()->id])->all());

        return $this->response(
            new PredefinedMailTemplateResource($this->repository->with('user')->find($template->id)),
            201
        );
    }

    /**
     * Update the specified mail template in storage
     *
     * @param int $id
     * @param \App\Http\Requests\PredefinedMailTemplateRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, PredefinedMailTemplateRequest $request)
    {
        $this->authorize('update', $this->repository->find($id));

        $template = $this->repository->update($request->except('user_id'), $id);

        return $this->response(
            new PredefinedMailTemplateResource($this->repository->with('user')->find($template->id))
        );
    }

    /**
     * Remove the specified mail template from storage
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', $this->repository->find($id));

        $this->repository->delete($id);

        return $this->response('', 204);
    }
}
