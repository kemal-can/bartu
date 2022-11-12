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

namespace App\Innoclapps\Models;

use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Import extends Model
{
    const STATUSES = [
        'mapping'     => 1,
        'in-progress' => 2,
        'finished'    => 3,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_path',
        'resource_name',
        'status',
        'imported',
        'skipped',
        'duplicates',
        'user_id',
        'completed_at',
        'data',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'data'       => 'array',
        'user_id'    => 'int',
        'duplicates' => 'int',
        'skipped'    => 'int',
        'imported'   => 'int',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::disk(static::disk())->deleteDirectory(
                pathinfo($model->file_path, PATHINFO_DIRNAME)
            );
        });
    }

    /**
     * An Import has user/creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            Innoclapps::getUserRepository()->model()
        );
    }

    /**
     * Get the import storage disk
     *
     * @return string
     */
    public static function disk()
    {
        return 'local';
    }

    /**
     * Get the fields intended for this import
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function fields()
    {
        Innoclapps::setImportStatus('mapping');

        return Innoclapps::resourceByName($this->resource_name)
            ->importable()
            ->resolveFields();
    }

    /**
     * Get the file name attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fileName() : Attribute
    {
        return Attribute::get(fn () => basename($this->file_path));
    }

    /**
     * Get the import's status.
     */
    protected function status() : Attribute
    {
        return new Attribute(
            get: fn ($value) => array_search($value, static::STATUSES),
            set: fn ($value) => static::STATUSES[
                is_numeric($value) ? array_search($value, static::STATUSES) : $value
            ]
        );
    }
}
