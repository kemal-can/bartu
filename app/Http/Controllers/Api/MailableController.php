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
use App\Http\Controllers\ApiController;
use App\Http\Resources\MailableResource;
use App\Innoclapps\Contracts\Repositories\MailableRepository;

class MailableController extends ApiController
{
    /**
     * Initialize new MailableController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\MailableRepository $repository
     */
    public function __construct(protected MailableRepository $repository)
    {
    }

    /**
     * Retrieve all mail templates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->response(
            MailableResource::collection($this->repository->orderBy('name')->all())
        );
    }

    /**
     * Retrieve mail templates in specific locale
     *
     * @param string $locale
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forLocale($locale)
    {
        return $this->response(
            MailableResource::collection($this->repository->orderBy('name')->forLocale($locale))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->response(new MailableResource(
            $this->repository->find($id)
        ));
    }

    /**
     * Update the specified mail template in storage
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'subject'       => 'required|string|max:191',
            'html_template' => 'required|string',
        ]);

        $template = $this->repository->update($request->all(), $id);

        return $this->response(new MailableResource($template));
    }
}
