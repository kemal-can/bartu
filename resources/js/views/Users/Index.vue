<template>
  <div class="mb-5 flex items-center justify-between">
    <h3
      class="whitespace-nowrap text-lg font-medium leading-6 text-neutral-700 dark:text-white"
      v-t="'user.users'"
    />
    <div class="space-x-3">
      <i-button
        variant="secondary"
        size="sm"
        icon="Mail"
        :disabled="!componentReady"
        :to="{ name: 'invite-user' }"
        >{{ $t('user.invite') }}</i-button
      >
      <i-button
        variant="primary"
        size="sm"
        icon="Plus"
        :disabled="!componentReady"
        :to="{ name: 'create-user' }"
        >{{ $t('user.create') }}</i-button
      >
    </div>
  </div>

  <resource-table
    resource-name="users"
    ref="table"
    :table-id="tableId"
    @loaded="componentReady = true"
    :with-customize-button="true"
  >
    <template #name="{ row, formatted }">
      <router-link
        class="link"
        :to="{ name: 'edit-user', params: { id: row.id } }"
      >
        <i-avatar
          size="xs"
          :src="row.avatar_url"
          :title="row.name"
          class="mr-1"
        />
        {{ formatted }}
      </router-link>
    </template>
  </resource-table>
  <!-- Create, Edit -->
  <router-view
    name="createEdit"
    @created="reloadTable"
    @updated="reloadTable"
    @hidden="$router.push({ name: 'users-index' })"
  />
  <router-view name="invite" />
</template>
<script>
import ResourceTable from '@/components/Table'

export default {
  components: { ResourceTable },
  data: () => ({
    tableId: 'users',
    componentReady: false,
  }),
  methods: {
    /**
     * Reload the table
     */
    reloadTable() {
      this.$iTable.reload(this.tableId)
    },

    /**
     * Handles table action executed event
     * The function refreshes the store when a user(s) is deleted
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    actionExecuted(action) {
      if (action.destroyable) {
        action.ids.forEach(id => this.$store.commit('users/REMOVE', id))
      }
    },
  },
  mounted() {
    Innoclapps.$on('action-executed', this.actionExecuted)
  },
  unmounted() {
    /**
     * Reset the store state
     *
     * We need to reset the state in case changes are performed
     * because of the local cached data for the users
     */
    this.resetStoreState()
    Innoclapps.$off('action-executed', this.actionExecuted)
  },
}
</script>
