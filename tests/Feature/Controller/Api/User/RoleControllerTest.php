<?php

namespace Tests\Feature\Controller\Api\User;

use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_roles_endpoints()
    {
        $this->getJson('/api/roles')->assertUnauthorized();
        $this->getJson('/api/roles/FAKE_ID')->assertUnauthorized();
        $this->postJson('/api/roles')->assertUnauthorized();
        $this->putJson('/api/roles/FAKE_ID')->assertUnauthorized();
        $this->deleteJson('/api/roles/FAKE_ID')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_roles_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/roles')->assertForbidden();
        $this->getJson('/api/roles/FAKE_ID')->assertForbidden();
        $this->postJson('/api/roles')->assertForbidden();
        $this->putJson('/api/roles/FAKE_ID')->assertForbidden();
        $this->deleteJson('/api/roles/FAKE_ID')->assertForbidden();
    }

    public function test_user_can_retrieve_all_roles()
    {
        $this->signIn();

        $this->createRole('admin');
        $this->createRole('writer');
        $this->createRole('manager');

        $this->getJson('/api/roles')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_create_role()
    {
        $this->signIn();

        $this->postJson('/api/roles', ['name' => 'Role'])
            ->assertStatus(201)
            ->assertJson(['name' => 'Role']);
    }

    public function test_user_can_update_role()
    {
        $this->signIn();

        $role = $this->createRole();

        $this->putJson(
            '/api/roles/' . $role->id,
            ['name' => 'Changed']
        )
            ->assertOk()
            ->assertJson(['name' => 'Changed']);
    }

    public function test_user_can_retrieve_role()
    {
        $this->signIn();

        $role = $this->createRole();

        $this->getJson('/api/roles/' . $role->id)->assertJson([
            'id'   => $role->id,
            'name' => $role->name,
        ]);
    }

    public function test_user_can_delete_role()
    {
        $this->signIn();

        $role = $this->createRole();

        $this->deleteJson('/api/roles/' . $role->id)->assertNoContent();
        $this->assertModelMissing($role);
    }

    public function test_role_can_have_permissions()
    {
        $this->signIn();

        $permission = $this->createPermission();

        $this->postJson('/api/roles', [
            'name'        => 'Role',
            'permissions' => [
                $permission->name,
            ],
        ])->assertStatus(201)
            ->assertJsonCount(1, 'permissions')
            ->assertJsonPath('permissions.0.name', $permission->name);
    }

    public function test_role_permissions_can_be_updated()
    {
        $this->signIn();

        $role        = $this->createRole();
        $permissions = [
            $this->createPermission('dummy-permission-1'),
            $this->createPermission('dummy-permission-2'),
            $this->createPermission('dummy-permission-3'),
        ];

        $role->givePermissionTo([
            $permissions[0]->name,
            $permissions[1]->name,
        ]);

        $this->putJson('/api/roles/' . $role->id, [
            'name'        => $role->name,
            'permissions' => [$newPermissionName = $permissions[2]->name],
        ])->assertOk()
            ->assertJsonCount(1, 'permissions')
            ->assertJsonPath('permissions.0.name', $newPermissionName);
    }

    public function test_role_requires_name()
    {
        $this->signIn();

        $this->postJson('/api/roles', ['name' => ''])
            ->assertJsonValidationErrors(['name']);

        $this->putJson('/api/roles/FAKE_ID')
            ->assertJsonValidationErrors(['name']);
    }

    public function test_role_name_must_be_unique()
    {
        $this->signIn();

        $roles = [
            $this->createRole('role-name-1'),
            $this->createRole('role-name-2'),
        ];

        $this->postJson('/api/roles', ['name' => $roles[0]->name])
            ->assertJsonValidationErrors(['name']);

        $this->putJson('/api/roles/' . $roles[1]->id, ['name' => $roles[0]->name])
            ->assertJsonValidationErrors(['name']);
    }
}
