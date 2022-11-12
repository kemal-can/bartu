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

use App\Models\Source;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\SourceRepository;

class SourceRepositoryEloquent extends AppRepository implements SourceRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'name' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Source::class;
    }

    /**
     * Find source by given flag
     *
     * @param string $flag
     *
     * @return \App\Models\Source|null
     */
    public function findByFlag(string $flag) : ?Source
    {
        return $this->limit(1)->findByField('flag', $flag)->first();
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->isPrimary()) {
                abort(409, __('source.delete_primary_warning'));
            }

            if ($model->contacts()->count() > 0 || $model->companies()->count() > 0) {
                abort(409, __(
                    'resource.associated_delete_warning',
                    [
                    'resource' => __('sources.source'),
                ]
                ));
            }

            $model->contacts()->onlyTrashed()->update(['source_id' => null]);
            $model->companies()->onlyTrashed()->update(['source_id' => null]);
        });
    }
}
