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

use Closure;
use Illuminate\Support\Arr;
use App\Innoclapps\Models\Permission;
use App\Innoclapps\Contracts\Repositories\PermissionRepository;

class Manager
{
    /**
     * Hold all groups and permissions
     *
     * @var array
     */
    private array $permissions = [];

    /**
     * Helper property for handling the group stacks
     *
     * @var array
     */
    private array $groupStack = [];

    /**
     * Register permissions with group
     *
     * @param string|array $group
     * @param \Closure $registrator
     *
     * @return void
     */
    public function group($group, Closure $registrator)
    {
        $this->updateGroupStack($group);
        // Once we have updated the group stack, we'll load the provided permissions and
        // merge in the group's attributes when the permissions are created. After we
        // have created the permissions, we will pop the attributes off the stack.
        $this->loadPermissions($registrator);

        array_pop($this->groupStack);
    }

    /**
     * Register the permissions to the group stack
     *
     * @param array $data The permissions config
     * @param string $group The permissions group
     *
     * @return void
     */
    public function register($group, array $data)
    {
        if ($this->hasGroupStack()
            && $lastStack = $this->getLastGroupStack()) {
            $this->checkPermissionGroupArray($lastStack);
            $resourceName = $lastStack['name'];

            $this->configurePermissionsGroup($group, $resourceName, $data);
        }
    }

    /**
     * Create the missing permissions from database
     *
     * @param string $guard
     *
     * @return void
     */
    public function createMissing($guard = 'api')
    {
        // Do not resolve the repository in the class __construct as it
        // causes issue with the installer when service provider register permissions via the boot method
        resolve(PermissionRepository::class)->unguarded(function ($instance) use ($guard) {
            foreach ($this->all() as $permission) {
                $instance->firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }
        });
    }

    /**
     * Get all permissions in a group stack
     *
     * @return array
     */
    public function grouped()
    {
        return $this->permissions;
    }

    /**
     * Get all internal permission names
     * Includes only the permissions keys
     *
     * e.q. ['view_contacts', 'delete_contacts']
     *
     * @return array
     */
    public function all()
    {
        $allByGroups = data_get($this->permissions, '*.groups.*.permissions');

        return array_keys(Arr::collapse($allByGroups));
    }

    /**
     * Get all internal permission names with label
     *
     * e.q.  ['delete_contact' => 'Delete', 'view_contacts'=> 'View Contacts']
     *
     * @return array
     */
    public function keyed()
    {
        $allByGroups = data_get($this->permissions, '*.groups.*.permissions');

        return Arr::collapse($allByGroups);
    }

    /**
     * Determine if the permissions currently has a group stack.
     *
     * @return boolean
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Get the last permissions group stack
     *
     * @return string|null
     */
    public function getLastGroupStack()
    {
        return $this->groupStack[0] ?? null;
    }

    /**
     * Performs checks for the permission group array and sets default values
     *
     * @param array $lastStack Last Group Stack
     * @return null
     */
    protected function checkPermissionGroupArray($lastStack)
    {
        if (! array_key_exists($lastStack['name'], $this->permissions)) {
            $this->permissions[$lastStack['name']]           = [];
            $this->permissions[$lastStack['name']]['groups'] = [];
            $this->permissions[$lastStack['name']]['as']     = $lastStack['as'];
        }
    }

    /**
     * Update the group stack with the given name.
     *
     * @param string $stack
     * @return void
     */
    protected function updateGroupStack($stack)
    {
        $groupStack = ! is_array($stack)
        ? [
            'name' => $stack,
            'as'   => $stack,
        ]
        : $stack;

        $this->groupStack[] = $groupStack;
    }

    /**
     * Load the provided permissions.
     *
     * @param \Closure $permissions
     *
     * @return void
     */
    protected function loadPermissions($permissions)
    {
        $permissions($this);
    }

    /**
     * Register new permissions group under the given resource group
     *
     * @param string $groupName
     * @param string $resourceName
     * @param array $data
     *
     * @return void
     */
    protected function configurePermissionsGroup($groupName, $resourceName, $data)
    {
        /**
         * We will check if the group already exists, user is able to add additional permissions to an existing
         * just by providing the permissions parameter, note that the user is not allowed to modify other
         * parameters that were passed to the original group
         *
         * Sample usage:
         *
         *  Permissions::group($this->name(), function ($manager) {
         *   $manager->register('view', [
         *       'permissions' => [
         *           'view own attendes' => 'Where attending',
         *       ],
         *   ]);
         * });
        */

        $groupsKeys         = array_column($this->permissions[$resourceName]['groups'], 'group');
        $existingGroupIndex = array_search($groupName, $groupsKeys);

        if ($existingGroupIndex !== false) {
            $group                = $this->permissions[$resourceName]['groups'][$existingGroupIndex];
            $group['permissions'] = array_merge($group['permissions'], $data['permissions']);
            $group['keys']        = array_keys($group['permissions']);
            $group['single']      = count($group['keys']) === 1;

            $this->permissions[$resourceName]['groups'][$existingGroupIndex] = $group;
        } else {
            $keys       = array_keys($data['permissions']);
            $single     = count($keys) === 1;
            $revokeable = $data['revokeable'] ?? $single;  // Single permission groups can be revoked

            $this->permissions[$resourceName]['groups'][] = [
                'revokeable'  => $revokeable, // Whether the user can revoke the permission
                'keys'        => $keys, // Keys are the permission internal names e.q. ['delete', 'create','view'];
                'single'      => $single,
                'permissions' => $data['permissions'], // The available permissions for this group
                'as'          => $data['as'] ?? null, // The group name to show e.q. View
                'group'       => $groupName, // The group ID
            ];
        }
    }
}
