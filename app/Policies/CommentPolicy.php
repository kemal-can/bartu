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

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the comment.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Comment $comment
     *
     * @return boolean
     */
    public function view(User $user, Comment $comment)
    {
        return (int) $user->id === (int) $comment->created_by;
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Comment $comment
     *
     * @return boolean
     */
    public function update(User $user, Comment $comment)
    {
        return (int) $user->id === (int) $comment->created_by;
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Comment $comment
     *
     * @return boolean
     */
    public function delete(User $user, Comment $comment)
    {
        return (int) $user->id === (int) $comment->created_by;
    }
}
