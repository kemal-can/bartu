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

namespace App\Support\Concerns;

use App\Models\Comment;

trait HasComments
{
    /**
     * Boot the HasComments trait
     *
     * @return void
     */
    protected static function bootHasComments()
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->comments->each->delete();
            }
        });
    }

    /**
     * Get all of the model comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at');
    }
}
