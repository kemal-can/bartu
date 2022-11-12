<template>
  <resource-table
    v-if="initialize"
    resource-name="deals"
    :row-class="rowClass"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-deal' },
      title: 'No deals',
      buttonText: 'Create Deal',
      description: 'Get started by creating a new deal.',
    }"
    @loaded="$emit('loaded', $event)"
    v-bind="$attrs"
  >
    <template #name="{ row, formatted }">
      <div class="flex w-full justify-between">
        <router-link
          class="link grow"
          :to="{ name: 'view-deal', params: { id: row.id } }"
          >{{ formatted }}</router-link
        >
        <div class="ml-2">
          <i-minimal-dropdown>
            <i-dropdown-item
              @click="activityBeingCreatedRow = row"
              :text="$t('activity.create')"
            />
            <i-dropdown-item
              @click="preview(row.id)"
              :text="$t('app.preview')"
            />
            <i-dropdown-item
              v-if="row.authorizations.delete"
              @click="remove(row.id)"
              :text="$t('app.delete')"
            />
          </i-minimal-dropdown>
        </div>
      </div>
    </template>
    <template #status="{ row, column, formatted }">
      <i-badge
        :variant="column.badgeVariants[row.displayAs.status]"
        :rounded="false"
        class="rounded pb-1"
        >{{ $t('deal.status.' + formatted) }}</i-badge
      >
    </template>
  </resource-table>

  <create-activity
    :visible="activityBeingCreatedRow !== null"
    :hide-on-created="true"
    :deals="[activityBeingCreatedRow]"
    @created="reload"
    @hidden="activityBeingCreatedRow = null"
  />
  <preview-modal />
</template>
<script>
import ResourceTable from '@/components/Table'
import CreateActivity from '@/views/Activity/CreateSimple'
import { mapActions } from 'vuex'

export default {
  inheritAttrs: false,
  emits: ['deleted', 'loaded'],
  components: { ResourceTable, CreateActivity },
  props: {
    initialize: { default: true, type: Boolean },
  },
  data: () => ({
    tableId: 'deals',
    activityBeingCreatedRow: null,
  }),
  methods: {
    ...mapActions('deals', ['destroy', 'preview']),

    /**
     * Table row class
     */
    rowClass(row) {
      return {
        'has-warning': true,
        'warning-confirmed': row.falls_behind_expected_close_date === true,
      }
    },

    /**
     * Reload the table
     */
    reload() {
      this.$iTable.reload(this.tableId)
    },

    /**
     * Customize the table (refs usage)
     */
    customize() {
      this.$store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
        id: this.tableId,
        value: true,
      })
    },

    /**
     * Remove deal from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    remove(id) {
      this.destroy(id).then(() => {
        this.$emit('deleted', id)
        this.reload()
        Innoclapps.success(this.$t('resource.deleted'))
      })
    },
  },
}
</script>
