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

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\WebFormRequest;
use App\Http\Resources\WebFormResource;
use App\Contracts\Repositories\WebFormRepository;

class WebFormController extends Controller
{
    /**
     * @codeCoverageIgnore
     *
     * Display the web form
     *
     * @param string $uuid
     * @param \App\Contracts\Repositories\WebFormRepository $repository
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show($uuid, WebFormRepository $repository, Request $request)
    {
        $form = $repository->findByUuidForDisplay($uuid);
        abort_if(is_null($form) || (! Auth::check() && ! $form->isActive()), 404);

        $form  = new WebFormResource($form);
        $title = $form->sections[0]['title'] ?? __('form.form');

        return view('webforms.view', compact('form', 'title'));
    }

    /**
     * Process the webform request
     *
     * @param string $uuid
     * @param \App\Contracts\Repositories\WebFormRepository $repository
     * @param \App\Http\Requests\WebFormRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($uuid, WebFormRepository $repository, WebFormRequest $request)
    {
        $repository->processSubmission($request);

        return response()->json('', 204);
    }
}
