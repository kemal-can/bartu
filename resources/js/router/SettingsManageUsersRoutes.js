/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import i18n from '@/i18n'

import UserCreate from '@/views/Users/Create'
import UserEdit from '@/views/Users/Edit'
import Invite from '@/views/Users/Invite'
import RoleIndex from '@/views/Roles/Index'
import RoleCreate from '@/views/Roles/Create'
import RoleEdit from '@/views/Roles/Edit'
import ManageTeams from '@/views/Users/ManageTeams'

import SettingsManageUsers from '@/views/Settings/SettingsManageUsers'

export default [
  {
    path: 'users',
    component: SettingsManageUsers,
    name: 'users-index',
    meta: { title: i18n.t('user.users') },
    children: [
      {
        path: 'create',
        name: 'create-user',
        components: {
          createEdit: UserCreate,
        },
        meta: { title: i18n.t('user.create') },
      },
      {
        path: ':id/edit',
        name: 'edit-user',
        components: {
          createEdit: UserEdit,
        },
        meta: { title: i18n.t('user.edit') },
      },
      {
        path: 'invite',
        name: 'invite-user',
        components: {
          invite: Invite,
        },
        meta: { title: i18n.t('user.invite') },
      },
      {
        path: 'roles',
        name: 'role-index',
        components: {
          roles: RoleIndex,
        },
        meta: {
          title: i18n.t('role.roles'),
        },
        children: [
          {
            path: 'create',
            name: 'create-role',
            component: RoleCreate,
            meta: { title: i18n.t('role.create') },
          },
          {
            path: ':id/edit',
            name: 'edit-role',
            component: RoleEdit,
            meta: { title: i18n.t('role.edit') },
          },
        ],
      },
      {
        path: 'teams',
        name: 'manage-teams',
        components: {
          teams: ManageTeams,
        },
        meta: {
          title: i18n.t('team.teams'),
        },
      },
    ],
  },
]
