<template>
  <i-form-group label-for="name" :label="$t('role.name')" required>
    <i-form-input
      v-model="form.name"
      id="name"
      ref="name"
      name="name"
      type="text"
    >
    </i-form-input>
    <form-error :form="form" field="name" />
  </i-form-group>
  <i-overlay :show="permissionsLoading" class="mt-5">
    <div v-show="permissions.all.length > 0">
      <h3
        class="my-4 whitespace-nowrap text-lg font-medium leading-6 text-neutral-700 dark:text-neutral-200"
        v-t="'role.permissions'"
      ></h3>

      <div
        v-for="(featureGroup, index) in permissions.grouped"
        :key="index"
        class="mb-4"
      >
        <p class="mb-1 font-medium text-neutral-700 dark:text-neutral-200">
          {{ featureGroup.as }}
        </p>
        <div
          v-for="permissionGroup in featureGroup.groups"
          :key="permissionGroup.group"
        >
          <div class="flex justify-between">
            <p class="text-sm text-neutral-600 dark:text-neutral-300">
              {{
                permissionGroup.as
                  ? permissionGroup.as
                  : permissionGroup.single
                  ? permissionGroup.permissions[permissionGroup.keys[0]]
                  : ''
              }}
            </p>

            <i-dropdown placement="bottom-end" :full="false">
              <template #toggle>
                <button
                  type="button"
                  class="link inline-flex items-center text-sm"
                >
                  {{ getSelectedPermissionTextByGroup(permissionGroup) }}
                  <icon icon="ChevronDown" class="ml-1 h-5 w-5" />
                </button>
              </template>

              <div class="py-1">
                <i-dropdown-item
                  @click="revokePermission(permissionGroup)"
                  v-show="permissionGroup.revokeable"
                  :text="$t('role.revoked')"
                />
                <i-dropdown-item
                  v-if="permissionGroup.single"
                  @click="
                    setSelectedPermission(
                      permissionGroup,
                      permissionGroup.keys[0]
                    )
                  "
                  :text="$t('role.granted')"
                />
                <i-dropdown-item
                  v-else
                  :disabled="selectedPermissions.indexOf(key) > -1"
                  @click="setSelectedPermission(permissionGroup, key)"
                  v-for="(permission, key) in permissionGroup.permissions"
                  :key="key"
                  :text="permission"
                />
              </div>
            </i-dropdown>
          </div>
        </div>
      </div>
    </div>
  </i-overlay>
</template>
<script>
import { mapState } from 'vuex'
export default {
  props: {
    // Whether the form is embedded in create view
    create: {
      type: Boolean,
      default: false,
    },
    form: {
      required: true,
      type: Object,
      default: () => {},
    },
  },
  data: () => ({
    selectedPermissions: [],
    permissionsLoading: false,
    permissions: {
      all: [],
      grouped: {},
    },
  }),
  methods: {
    getSelectedPermissionTextByGroup(group) {
      if (group.single) {
        if (this.selectedPermissions.indexOf(group.keys[0]) > -1) {
          return this.$t('role.granted')
        }
        return this.$t('role.revoked')
      }
      for (let i in group.keys) {
        if (this.selectedPermissions.indexOf(group.keys[i]) > -1) {
          return group.permissions[group.keys[i]]
        }
      }
      return this.$t('role.revoked')
    },
    setSelectedPermission(group, permissionKey) {
      // Revoke any previously group permissions
      this.revokePermission(group)
      // Now set the new selected permission
      this.selectedPermissions.push(permissionKey)
      // Update the form permissions with the new one
      this.$nextTick(() => {
        this.form.permissions = this.selectedPermissions
      })
    },
    revokePermission(group) {
      for (let key in group.keys) {
        let index = this.selectedPermissions.indexOf(group.keys[key])
        if (index != -1) {
          this.selectedPermissions.splice(index, 1)
        }
      }
    },
    setDefaultSelectedPermissions(permissions) {
      for (let featureGroup in permissions.grouped) {
        permissions.grouped[featureGroup].groups.forEach(permissionGroup => {
          if (!permissionGroup.single) {
            // When creating new role set the first permission as selected
            // This is applied if there is more then one available child permission for a group
            this.setSelectedPermission(
              permissionGroup,
              Object.keys(permissionGroup.permissions)[0]
            )
          }
        })
      }
    },
    fetchAndSetPermissions() {
      this.permissionsLoading = true
      Innoclapps.request()
        .get('/permissions')
        .then(({ data }) => {
          this.permissions = data

          if (!this.create) {
            this.selectedPermissions = this.form.permissions

            return
          }

          this.setDefaultSelectedPermissions(data)
        })
        .finally(() => (this.permissionsLoading = false))
    },
  },
  created() {
    this.fetchAndSetPermissions()
  },
}
</script>
