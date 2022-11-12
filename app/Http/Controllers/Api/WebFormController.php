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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Resources\WebFormResource;
use App\Innoclapps\Translation\Translation;
use App\Contracts\Repositories\WebFormRepository;

class WebFormController extends ApiController
{
    /**
     * Initialize new DealBoardController instance.
     *
     * @param \App\Contracts\Repositories\WebFormRepository $deals
     */
    public function __construct(protected WebFormRepository $repository)
    {
    }

    /**
     * Get all web forms
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $forms = $this->repository->withResponseRelations()->all();

        return $this->response(
            WebFormResource::collection($forms)
        );
    }

    /**
     * Display web form;
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $form = $this->repository->withResponseRelations()->find($id);

        return $this->response(new WebFormResource($form));
    }

    /**
     * Store a newly created web form in storage
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validateForm($request);
        $this->mergeDefaults($request);

        $form = $this->repository->create($request->all());

        return $this->response(
            new WebFormResource($this->repository->withResponseRelations()->find($form->id)),
            201
        );
    }

    /**
     * Update the specified web form in storage
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $this->validateForm($request);

        $form = $this->repository->update($request->all(), $id);

        return $this->response(
            new WebFormResource($this->repository->withResponseRelations()->find($form->id))
        );
    }

    /**
     * Remove the specified web form from storage
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $this->repository->delete($id);

        return $this->response('', 204);
    }

    /**
     * Merge the default data
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function mergeDefaults(Request $request)
    {
        $defaults = [
            'sections'      => [],
            'notifications' => [],
            'submit_data'   => [
                'action'        => 'message',
                'success_title' => 'Form submitted.',
            ],
            'status'  => 'active',
            'locale'  => Auth::user()->preferredLocale(),
            'user_id' => Auth::id(),
        ];

        foreach ($defaults as $attribute => $value) {
            if ($request->missing($attribute) || blank($request->{$attribute})) {
                $request->merge([$attribute => $value]);
            }
        }
    }

    /**
     * Validate the request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function validateForm(Request $request)
    {
        $request->validate([
            'locale'                           => ['sometimes', 'required', 'string', Rule::in(Translation::availableLocales())],
            'title'                            => ($request->isMethod('PUT') ? 'sometimes|'  : '') . 'required|string|max:191',
            'styles.primary_color'             => ($request->isMethod('PUT') ? 'sometimes|'  : '') . 'required|max:7',
            'styles.background_color'          => ($request->isMethod('PUT') ? 'sometimes|'  : '') . 'required|max:7',
            'notifications.*'                  => 'sometimes|email',
            'submit_data.pipeline_id'          => 'sometimes|required',
            'submit_data.stage_id'             => 'sometimes|required',
            'submit_data.action'               => 'sometimes|required|in:message,redirect',
            'submit_data.success_title'        => 'required_if:submit_data.action,message',
            'submit_data.success_redirect_url' => 'nullable|required_if:submit_data.action,redirect|url',
            'user_id'                          => 'sometimes|required',
        ]);
    }
}
