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

use App\Models\LostReason;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\LostReasonRepository;

class LostReasonRepositoryEloquent extends AppRepository implements LostReasonRepository
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
        return LostReason::class;
    }
}
