<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Tests\TestCase;

class CommentTest extends TestCase
{
    public function test_comment_has_commentables()
    {
        $comment = new Comment;

        $this->assertInstanceOf(MorphTo::class, $comment->commentable());
    }
}
