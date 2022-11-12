<template>
  <div class="mb-2 block lg:hidden">
    <activity-table-type-picker v-model="selectedType" />
  </div>
  <resource-table
    v-if="initialize"
    resource-name="activities"
    :row-class="rowClass"
    :data-request-query-string="dataRequestQueryString"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-activity' },
      title: 'No activities',
      buttonText: 'Create Activity',
      description: 'Get started by creating a new activity.',
    }"
    v-bind="$attrs"
  >
    <template #after-search>
      <div class="hidden lg:ml-6 lg:block">
        <activity-table-type-picker v-model="selectedType" />
      </div>
    </template>
    <template #type="{ row, formatted }">
      <!-- When switching the activity type column from the table visibility
      the row.type is not available and may cause an error, in this case, we check if it's actually available
      just before refreshing the table after saving the settings, test: switch off, switch on, error shown -->
      <text-background
        v-if="row.type"
        :color="row.type.swatch_color"
        class="inline-flex items-center justify-center rounded-full px-2.5 text-sm font-normal leading-5 dark:!text-white"
      >
        <icon :icon="row.type.icon" class="mr-1 h-4 w-4 text-current" />
        {{ formatted }}
      </text-background>
    </template>
    <template #title="{ row, formatted }">
      <div class="flex w-full justify-between">
        <router-link
          class="link grow"
          :to="{ name: 'edit-activity', params: { id: row.id } }"
          >{{ formatted }}</router-link
        >
        <div class="ml-2">
          <i-minimal-dropdown>
            <i-dropdown-item
              v-if="row.authorizations.delete"
              @click="remove(row.id)"
              :text="$t('app.delete')"
            />
          </i-minimal-dropdown>
        </div>
      </div>
    </template>
  </resource-table>
  <!-- Edit/View -->
  <router-view name="edit"></router-view>
</template>
<script>
import { mapActions } from 'vuex'
import TextBackground from '@/components/TextBackground'
import ResourceTable from '@/components/Table'
import ActivityTableTypePicker from './TableTypePicker'
export default {
  inheritAttrs: false,
  emits: ['deleted'],
  components: {
    ResourceTable,
    ActivityTableTypePicker,
    TextBackground,
  },
  props: {
    initialize: { default: true, type: Boolean },
  },
  data: () => ({
    tableId: 'activities',
    selectedType: undefined,
  }),
  computed: {
    /**
     * Get the custom query string for the table
     *
     * @return {Object}
     */
    dataRequestQueryString() {
      return {
        activity_type_id: this.selectedType,
      }
    },
  },
  methods: {
    ...mapActions('activities', ['destroy']),

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
     * Table row class
     */
    rowClass(row) {
      return {
        'has-warning': true,
        'warning-confirmed': row.is_due,
      }
    },

    /**
     * Remove activity from storage
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
