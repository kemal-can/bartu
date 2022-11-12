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

namespace App\Innoclapps\Permissions;

use App\Innoclapps\Models\Permission;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\PermissionRepository;

class PermissionRepositoryEloquent extends AppRepository implements PermissionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Permission::class;
    }
}
