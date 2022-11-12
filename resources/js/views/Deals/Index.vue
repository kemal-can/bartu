<template>
  <i-layout>
    <cards resource-name="deals" v-if="showCards" />

    <template #actions>
      <navbar-separator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <i-minimal-dropdown type="horizontal">
            <i-dropdown-item
              icon="DocumentAdd"
              :to="{
                name: 'import-deal',
              }"
              :text="$t('import.import')"
            />

            <i-dropdown-item
              icon="DocumentDownload"
              v-i-modal="'export-modal'"
              >{{ $t('app.export.export') }}</i-dropdown-item
            >
            <i-dropdown-item
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName: 'deals' },
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
            :to="{ name: 'deal-index' }"
            v-i-tooltip="$t('app.list_view')"
            variant="white"
            icon="ViewList"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
          />
          <i-button
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'deal-board' }"
            v-i-tooltip="$t('board.board')"
            variant="white"
            icon="ViewBoards"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
        </i-button-group>

        <i-button :to="{ name: 'create-deal' }" icon="Plus" size="sm">{{
          $t('deal.create')
        }}</i-button>
      </div>
    </template>

    <deal-table
      ref="table"
      :initialize="shouldInitializeIndex"
      @loaded="tableEmpty = $event.empty"
      @deleted="refreshIndex"
      :filter-id="
        $route.query.filter_id ? Number($route.query.filter_id) : undefined
      "
    />

    <deal-export
      url-path="/deals/export"
      resource-name="deals"
      :filters-view="tableId"
      :title="$t('deal.export')"
    />

    <!-- Create -->
    <router-view name="create" @created="refreshIndex"></router-view>
  </i-layout>
</template>
<script>
import DealTable from './DealTable'
import Cards from '@/components/Cards/Cards'
import DealExport from '@/components/Export'

const subRoutes = ['create-deal']
const boardRoute = 'deal-board'

export default {
  components: {
    DealTable,
    Cards,
    DealExport,
  },
  data: () => ({
    shouldInitializeIndex: false,
    tableId: 'deals',
    tableEmpty: true,
  }),
  computed: {
    /**
     * Indicates whether the cards should be shown
     *
     * @return {Boolean}
     */
    showCards() {
      return this.shouldInitializeIndex && !this.tableEmpty
    },
  },
  methods: {
    /**
     * Refresh the index
     *
     * @return {Void}
     */
    refreshIndex() {
      Innoclapps.$emit('refresh-cards')
      this.$refs.table.reload()
    },
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
  beforeRouteEnter(to, from, next) {
    // This is not available in beforeRouteEnter
    // We need to import the store separately
    localForage.getItem('deals-board-view-default').then((value, err) => {
      let isDefault = !err && value === true

      // Check if the deals board is active
      if (
        isDefault &&
        from.name != boardRoute &&
        subRoutes.indexOf(to.name) === -1
      ) {
        next({ name: boardRoute, query: to.query })

        return
      }

      /**
       * We will check whether the accessed route is the index one
       * Can be created, etc... in this case, we just load
       * the child route instead of loading all related data to the index
       */
      next(vm => {
        vm.shouldInitializeIndex = vm.$route.name === 'deal-index'
      })
    })
  },
  mounted() {
    /**
     * Keeps the board view active when only user want to create or preview deal
     * by accessing the routes not from index components
     *
     */
    if (subRoutes.indexOf(this.$route.name) === -1) {
      localForage.setItem('deals-board-view-default', false)
    }
  },
}
</script>
