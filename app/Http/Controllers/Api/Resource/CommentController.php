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

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Innoclapps\Facades\Innoclapps;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CommentResource;
use App\Contracts\Repositories\CommentRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;

class CommentController extends ApiController
{
    /**
     * Initialize new CommentController instance.
     *
     * @param \App\Contracts\Repositories\CommentRepository $repository
     */
    public function __construct(protected CommentRepository $repository)
    {
    }

    /**
    * Display the resource comments
    *
    * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(ResourceRequest $request)
    {
        $this->authorize('view', $request->record());

        return $this->response(
            CommentResource::collection(
                $request->record()->comments()
                    ->with('creator')
                    ->orderBy('created_at')
                    ->get()
            )
        );
    }

    /**
     * SAdd new resource comment
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ResourceRequest $request)
    {
        abort_unless(
            in_array(
                $request->resource()->name(),
                ['activities', 'notes', 'calls']
            ),
            404,
            'Comments cannot be added to the provided resource.'
        );

        $input = $request->validate([
            'body'            => 'required|string',
            'via_resource'    => 'sometimes|required_with:via_resource_id|string|in:contacts,companies,deals',
            'via_resource_id' => [
                'sometimes',
                'numeric',
                'required_with:via_resource',
                Rule::requiredIf(in_array($request->resource()->name(), ['notes', 'calls'])),
            ],
        ]);

        // When the via_resource is not provided, we will validate the actual resource
        // record, otherwise, we will validate the via_resource record e.q. user can see contact
        // and it's calls and a comment is added to the call
        if (! $request->has('via_resource')) {
            $this->authorize('view', $request->record());
        } else {
            $resource = Innoclapps::resourceByName($request->via_resource);
            $this->authorize('view', $resource->repository()->find($request->via_resource_id));
        }

        $comment = $this->repository->addComment($request->record(), $input);

        return $this->response(
            new CommentResource($comment),
            201
        );
    }

    /**
     * Display the given comment.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $comment = $this->repository->with('creator')->find($id);

        $this->authorize('view', $comment);

        return $this->response(new CommentResource($comment));
    }

    /**
     * Update the given comment
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $comment = $this->repository->find($id);
        $this->authorize('update', $comment);

        $input = $request->validate([
            'body'            => 'required|string',
            'via_resource'    => 'sometimes|required_with:via_resource_id|string|in:contacts,companies,deals',
            'via_resource_id' => [
                'sometimes',
                'numeric',
                'required_with:via_resource',
                Rule::requiredIf(in_array($comment->commentable->resource()->name(), ['notes', 'calls'])),
            ],
        ]);

        return $this->response(new CommentResource(
            $this->repository->update($input, $id)
        ));
    }

    /**
     * Remove the given comment from storage
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $comment = $this->repository->find($id);

        $this->authorize('delete', $comment);

        $this->repository->delete($id);

        return $this->response('', 204);
    }
}
