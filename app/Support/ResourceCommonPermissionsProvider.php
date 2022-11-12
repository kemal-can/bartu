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

namespace App\Support;

use App\Innoclapps\Facades\Permissions;

class ResourceCommonPermissionsProvider
{
    public function __invoke($resource)
    {
        Permissions::group(['name' => $resource->name(), 'as' => $resource->label()], function ($manager) use ($resource) {
            $manager->register('view', [
                'as'          => __('role.capabilities.view'),
                'permissions' => [
                    'view own ' . $resource->name() => __('role.capabilities.owning_only'),
                    'view all ' . $resource->name() => __('role.capabilities.all', ['resourceName' => $resource->label()]),
                ],
            ]);

            $manager->register('edit', [
                'as'          => __('role.capabilities.edit'),
                'permissions' => [
                    'edit own ' . $resource->name() => __('role.capabilities.owning_only'),
                    'edit all ' . $resource->name() => __('role.capabilities.all', ['resourceName' => $resource->label()]),
                ],
            ]);

            $manager->register('delete', [
                'as'          => __('role.capabilities.delete'),
                'revokeable'  => true,
                'permissions' => [
                    'delete own ' . $resource->name()         => __('role.capabilities.owning_only'),
                    'delete any ' . $resource->singularName() => __('role.capabilities.all', ['resourceName' => $resource->label()]),
                ],
            ]);

            $manager->register('bulk_delete', [
                'permissions' => [
                    'bulk delete ' . $resource->name() => __('role.capabilities.bulk_delete'),
                ],
            ]);
        });
    }
}
