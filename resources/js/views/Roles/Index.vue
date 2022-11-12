<template>
  <i-card :header="$t('role.roles')" no-body :overlay="rolesAreBeingFetched">
    <template #actions>
      <i-button
        v-show="hasRoles"
        icon="Plus"
        :to="{ name: 'create-role' }"
        size="sm"
        >{{ $t('role.create') }}</i-button
      >
    </template>
    <i-table v-if="hasRoles" class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" v-t="'app.id'" width="5%"></th>
          <th class="text-left" v-t="'role.name'"></th>
          <th class="text-left"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in roles" :key="role.id">
          <td v-text="role.id"></td>
          <td>
            <router-link
              class="link"
              :to="{ name: 'edit-role', params: { id: role.id } }"
              >{{ role.name }}</router-link
            >
          </td>
          <td class="flex justify-end">
            <i-minimal-dropdown>
              <i-dropdown-item
                :to="{ name: 'edit-role', params: { id: role.id } }"
              >
                {{ $t('app.edit') }}
              </i-dropdown-item>

              <i-dropdown-item @click="destroy(role.id)">
                {{ $t('app.delete') }}
              </i-dropdown-item>
            </i-minimal-dropdown>
          </td>
        </tr>
      </tbody>
    </i-table>
    <i-card-body v-else-if="!rolesAreBeingFetched">
      <i-empty-state
        :to="{ name: 'create-role' }"
        title="No roles"
        button-text="Create Role"
        description="Get started by creating a new role."
      />
    </i-card-body>
  </i-card>
  <router-view></router-view>
</template>
<script>
import { mapState } from 'vuex'
export default {
  data: () => ({
    rolesAreBeingFetched: true,
  }),
  computed: {
    ...mapState({
      roles: state => state.roles.collection,
    }),
    hasRoles() {
      return this.roles.length > 0
    },
  },
  methods: {
    destroy(id) {
      this.$store
        .dispatch('roles/destroy', id)
        .then(() => Innoclapps.success(this.$t('role.deleted')))
    },
  },
  created() {
    this.$store
      .dispatch('roles/fetch')
      .finally(() => (this.rolesAreBeingFetched = false))
  },
}
</script>
