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

use App\Models\Industry;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\IndustryRepository;

class IndustryRepositoryEloquent extends AppRepository implements IndustryRepository
{
    /**
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
        return Industry::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->companies()->count() > 0) {
                abort(409, __(
                    'resource.associated_delete_warning',
                    [
                    'resource' => __('company.industry.industry'),
                ]
                ));
            }

            $model->companies()->onlyTrashed()->update(['industry_id' => null]);
        });
    }
}
