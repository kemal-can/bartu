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

namespace App\Repositories;

use App\Models\Comment;
use App\Support\PendingMention;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Database\Eloquent\Model;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CommentRepository;

class CommentRepositoryEloquent extends AppRepository implements CommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Comment::class;
    }

    /**
     * Add new comment to the given commentable
     *
     * @param \Illuminate\Eloquent\Database\Model $commentable
     * @param array $attributes
     */
    public function addComment(Model $commentable, array $attributes) : Comment
    {
        $mention            = new PendingMention($attributes['body']);
        $attributes['body'] = $mention->getUpdatedText();

        $comment = $commentable->comments()->create($attributes);

        $this->notifyMentionedUsers(
            $mention,
            $comment,
            $attributes['via_resource'] ?? null,
            $attributes['via_resource_id'] ?? null
        );

        return $comment->loadMissing('creator');
    }

    /**
     * Update the given comment
     *
     * @param array $attributes
     * @param int $id
     *
     * @return \App\Models\Comment
     */
    public function update(array $attributes, $id)
    {
        $mention            = new PendingMention($attributes['body']);
        $attributes['body'] = $mention->getUpdatedText();

        $comment = parent::update($attributes, $id);

        $this->notifyMentionedUsers(
            $mention,
            $comment,
            $attributes['via_resource'] ?? null,
            $attributes['via_resource_id'] ?? null
        );

        return $comment->loadMissing('creator');
    }

    /**
    * Notify the mentioned users for the given mention
    *
    * @param \App\Support\PendingMention $mention
    * @param \App\Models\Comment $comment
    * @param string|null $viaResource
    * @param int|null $viaResourceId
    *
    * @return void
    */
    protected function notifyMentionedUsers(PendingMention $mention, $comment, $viaResource = null, $viaResourceId = null)
    {
        $isViaResource = $viaResource && $viaResourceId;

        $intermediate = $isViaResource ?
            Innoclapps::resourceByName($viaResource)->repository()->find($viaResourceId) :
            $comment->commentable;

        $mention->setUrl($intermediate->path)->withUrlQueryParameter([
            ...[
                'comment_id' => $comment->getKey(),
            ],
            ...array_filter([
                'section'    => $isViaResource ? $comment->commentable->resource()->name() : null,
                'resourceId' => $isViaResource ? $comment->commentable->getKey() : null,
            ]),
        ])->notify();
    }
}
