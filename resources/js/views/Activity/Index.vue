<template>
  <i-layout>
    <template #actions>
      <navbar-separator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <i-minimal-dropdown type="horizontal">
            <i-dropdown-item
              icon="DocumentAdd"
              :to="{
                name: 'import-resource',
                params: { resourceName: 'activities' },
              }"
              :text="$t('import.import')"
            />
            <i-dropdown-item
              icon="DocumentDownload"
              v-i-modal="'export-modal'"
              :text="$t('app.export.export')"
            />
            <i-dropdown-item
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName: 'activities' },
              }"
              >{{ $t('app.soft_deletes.trashed') }}</i-dropdown-item
            >
            <i-dropdown-item
              icon="Cog"
              @click="() => $refs.table.customize()"
              >{{ $t('table.list_settings') }}</i-dropdown-item
            >
          </i-minimal-dropdown>
        </div>

        <i-button-group class="mr-5">
          <i-button
            size="sm"
            class="relative bg-neutral-100 focus:z-10"
            :to="{ name: 'activity-index' }"
            v-i-tooltip="$t('app.list_view')"
            icon="ViewList"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
            variant="white"
          />
          <i-button
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'activity-calendar' }"
            v-i-tooltip="$t('calendar.calendar')"
            variant="white"
            icon="Calendar"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
        </i-button-group>

        <i-button :to="{ name: 'create-activity' }" icon="Plus" size="sm">{{
          $t('activity.create')
        }}</i-button>
      </div>
    </template>

    <activity-table
      ref="table"
      :initialize="shouldInitializeIndex"
      :filter-id="
        $route.query.filter_id ? Number($route.query.filter_id) : undefined
      "
    />

    <activity-export
      url-path="/activities/export"
      resource-name="activities"
      :filters-view="tableId"
      :title="$t('activity.export')"
    />
    <!-- Create -->
    <router-view name="create" @created="recordCreated"></router-view>
  </i-layout>
</template>
<script>
import ActivityTable from './ActivityTable'
import ActivityExport from '@/components/Export'
const subRoutes = ['create-activity', 'edit-activity', 'view-activity']
const calendarRoute = 'activity-calendar'

export default {
  components: {
    ActivityTable,
    ActivityExport,
  },
  data: () => ({
    tableId: 'activities',
    shouldInitializeIndex: false,
  }),
  methods: {
    /**
     * Handle activity created event
     *
     * @return {Void}
     */
    recordCreated() {
      if (this.shouldInitializeIndex) {
        this.$refs.table.reload()
      }
    },
  },
  beforeRouteEnter(to, from, next) {
    // This is not available in beforeRouteEnter
    // We need to import the store separately
    localForage.getItem('activity-calendar-view-default').then((value, err) => {
      let isDefault = !err && value === true

      // Check if the activities calendar view is active
      if (
        isDefault &&
        from.name != calendarRoute &&
        subRoutes.indexOf(to.name) === -1 &&
        // The calendar does not have filters, hence, it's not supported
        // for this reason, we will show the table view
        !to.query.filter_id
      ) {
        next({ name: calendarRoute })

        return
      }

      /**
       * We will check whether the accessed route is the index one
       * Can be created, preview etc... in this case, we just load
       * the child route instead of loading all related data to the index
       */
      next(vm => {
        vm.shouldInitializeIndex = vm.$route.name === 'activity-index'
      })
    })
  },
  /**
   * Before the cached route is updated
   * For all cases set that intialize index to be true
   * This helps when intially shouldInitializeIndex was false
   * But now when the user actually sees the index, it should be updated to true
   */
  beforeRouteUpdate(to, from, next) {
    this.shouldInitializeIndex = true

    next()
  },
  mounted() {
    /**
     * Keeps the calendar view active when only user want to create or preview activity
     * by accessing the routes not from index components
     *
     */
    if (subRoutes.indexOf(this.$route.name) === -1) {
      localForage.setItem('activity-calendar-view-default', false)
    }
  },
}
</script>
