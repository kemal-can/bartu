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

use App\Models\CallOutcome;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CallOutcomeRepository;

class CallOutcomeRepositoryEloquent extends AppRepository implements CallOutcomeRepository
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
        return CallOutcome::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->calls()->count() > 0) {
                abort(409, __('call.outcome.delete_warning'));
            }
        });
    }
}
