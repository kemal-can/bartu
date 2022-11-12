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

namespace App\Models;

use App\Innoclapps\Models\Model;
use App\Innoclapps\Concerns\HasCreator;
use App\Support\Concerns\HasEditorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory,
        HasEditorFields,
        HasCreator;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'created_by' => 'int',
    ];

    /**
     * Get the parent commentable model
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the fields that are used as editor
     *
     * @return string
     */
    public function getEditorFields() : string
    {
        return 'body';
    }
}
