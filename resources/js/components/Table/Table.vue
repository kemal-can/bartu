<template>
  <i-card v-if="showEmptyState" class="m-auto max-w-5xl">
    <i-empty-state v-bind="emptyState" />
  </i-card>

  <i-overlay :show="!initialDataLoaded && !showEmptyState">
    <div v-show="!showEmptyState && initialDataLoaded">
      <div class="mb-2 flex flex-wrap items-center md:mb-4 md:flex-nowrap">
        <div
          class="order-last w-full shrink-0 md:order-first md:mb-0 md:w-auto"
        >
          <input-search
            v-model="search"
            @input="request(true)"
            :disabled="searchDisabled"
          />
        </div>

        <!-- <table-per-page-options :collection="collection" class="ml-4" /> -->

        <slot name="after-search"></slot>
        <div class="flex w-full md:justify-end">
          <table-settings
            v-if="componentReady"
            :config="config"
            :with-customize-button="withCustomizeButton"
            :url-path="computedUrlPath"
            :table-id="tableId"
            :resource-name="resourceName"
          />
        </div>
      </div>

      <div
        class="mb-2 flex flex-col items-start md:mb-4 md:flex-row"
        v-if="componentReady && hasRules"
      >
        <div
          class="mb-2 flex w-full shrink-0 content-center space-x-1 sm:w-auto md:mb-0"
        >
          <filters-dropdown
            :view="filtersView"
            :identifier="filtersIdentifier"
            @apply="applyFilters"
            class="flex-1"
            placement="bottom-start"
          />

          <i-button
            variant="white"
            @click="toggleFiltersRules"
            v-show="hasRulesApplied && !rulesAreVisible"
            icon="PencilAlt"
          />

          <i-button
            variant="white"
            @click="toggleFiltersRules"
            v-show="!hasRulesApplied && !rulesAreVisible"
            icon="Plus"
          >
            {{ $t('filters.add_filter') }}
          </i-button>
        </div>

        <div class="ml-0 mb-2 md:ml-4 md:mb-0">
          <rules-display :identifier="filtersIdentifier" :view="filtersView" />
        </div>
      </div>

      <filters
        v-if="componentReady"
        :view="filtersView"
        :identifier="filtersIdentifier"
        :active-filter-id="filterId"
        @apply="applyFilters"
      />

      <i-card no-body :overlay="isLoading">
        <!-- When no maxHeight is provided, just set the maxHeight to big number e.q. 10000px
                    because when the user previous had height, and updated resetted the table, VueJS won't set the height to auto
                    or remove the previous height -->
        <i-table
          v-show="componentReady"
          :sticky="config.maxHeight !== null"
          :id="'table-' + tableId"
          class="rounded-lg"
          wrapper-class="-mt-px"
          :max-height="
            config.maxHeight !== null ? config.maxHeight + 'px' : '10000px'
          "
        >
          <thead>
            <tr>
              <header-cell
                v-for="(column, cidx) in visibleColumns"
                :key="'th-' + cidx"
                :column="column"
                :is-selectable="isSelectable"
                :all-rows-selected="allRowsSelected"
                :selected-rows="selectedRows"
                :resource-name="resourceName"
                :action-request-query-string="actionRequestQueryString"
                :actions="config.actions"
                :index="cidx"
                :is-ordered-by="attr => collection.isOrderedBy(attr)"
                :is-sorted="(dir, attr) => collection.isSorted(dir, attr)"
                @sort-requested="attr => collection.toggleSortable(attr)"
                @toggle-select-all="toggleSelectAll"
              >
                <template v-slot="slotProps">
                  <slot
                    :name="column.attribute + '-heading'"
                    :v-bind="slotProps"
                  >
                    {{ column.label }}
                  </slot>
                </template>
              </header-cell>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in collection.items"
              :key="index"
              :class="[
                rowClass ? rowClass(row) : undefined,
                row.tSelected && '!bg-neutral-50 dark:!bg-neutral-800',
              ]"
              @click="selectOnRowClick($event, row)"
            >
              <data-cell
                v-for="(column, cidx) in visibleColumns"
                :key="'td-' + cidx"
                :column="column"
                :row="row"
                :isSelectable="isSelectable"
                :index="cidx"
                @selected="onRowSelected"
              >
                <template v-slot="slotProps">
                  <slot v-bind="slotProps" :name="column.attribute">
                    <component
                      :is="column.component"
                      v-bind="slotProps"
                      :resource-name="resourceName"
                    />
                  </slot>
                </template>
              </data-cell>
            </tr>
            <tr v-if="!collection.hasItems">
              <td
                :colspan="totalColumns"
                class="p-5"
                v-if="!isLoading && initialDataLoaded"
                v-text="emptyText"
              />
            </tr>
          </tbody>
        </i-table>
      </i-card>
      <table-pagination
        class="mt-4 sm:mt-6"
        :collection="collection"
        :loading="isLoading"
      />
    </div>
  </i-overlay>
</template>
<script>
const qs = require('qs')
import ItSelectsRows from './ItSelectsRows'
import TablePagination from './TablePagination'
import InteractsWithData from './InteractsWithData'
import FiltersDropdown from '@/components/Filters/FiltersDropdown'
import Filters from '@/components/Filters'
import HandlesLoadingState from './HandlesLoadingState'
import Filterable from '@/components/Filters/Filterable'
import TableSettings from './TableSettings'
import TablePerPageOptions from '@/components/Table/TablePerPageOptions'
import TableDataColumn from './DataComponents/TableDataColumn'
import TableDataBooleanColumn from './DataComponents/TableDataBooleanColumn'
import TablePresentableDataColumn from './DataComponents/TablePresentableDataColumn'
import TableDataPhones from './DataComponents/TableDataPhones'
import TableDataEmail from './DataComponents/TableDataEmail'
import DataCell from './TableDataCell'
import HeaderCell from './TableHeaderCell'
import RulesDisplay from '@/components/QueryBuilder/RulesDisplay'
import { CancelToken } from '@/services/HTTP'

export default {
  emits: ['loaded'],
  components: {
    TablePagination,
    TablePerPageOptions,
    TableSettings,
    FiltersDropdown,
    Filters,
    TableDataColumn,
    TableDataBooleanColumn,
    TablePresentableDataColumn,
    TableDataPhones,
    TableDataEmail,
    DataCell,
    HeaderCell,
    RulesDisplay,
  },
  mixins: [ItSelectsRows, HandlesLoadingState, InteractsWithData, Filterable],
  data: () => ({
    search: '',
    componentReady: false,
    watchersInitialized: false,
    requestCancelToken: null,
    initialDataLoaded: false,
  }),
  props: {
    tableId: { type: String, required: true },
    resourceName: { type: String, required: true },
    actionRequestQueryString: {
      type: Object,
      default() {
        return {}
      },
    },
    dataRequestQueryString: {
      type: Object,
      default() {
        return {}
      },
    },
    withCustomizeButton: { type: Boolean, default: false },
    emptyState: Object,
    rowClass: Function,
    urlPath: String,
    /**
     * The filter id to intially apply to the table
     * If not provided, the default one will be used (if any)
     */
    filterId: Number,
  },
  computed: {
    /**
     * Get the text when the table is empty
     *
     * @return {String}
     */
    emptyText() {
      if (this.collection.hasItems) {
        return ''
      }

      if (this.isLoading) {
        return '...'
      }

      if (this.search) {
        return this.$t('app.no_search_results')
      }

      return this.$t('table.empty')
    },

    /**
     * Get the filters identifier
     */
    filtersIdentifier() {
      return this.config.identifier
    },

    /**
     * Get the filters view
     */
    filtersView() {
      return this.tableId
    },

    /**
     * Indicates whether the empty state should be shown
     *
     * @return {Boolean} [description]
     */
    showEmptyState() {
      // Indicates whether there is performed any request to the server for data
      if (typeof this.collection.state.meta.all_time_total == 'undefined') {
        return false
      }

      return this.isEmpty && this.emptyState != undefined
    },

    /**
     * Indicates whether the table has any results
     *
     * @return {Boolean}
     */
    isEmpty() {
      return this.collection.state.meta.all_time_total === 0
    },

    /**
     * The table settings
     *
     * @return {Object}
     */
    config() {
      return this.$store.state.table.settings[this.tableId] || {}
    },

    /**
     * Table request params
     *
     * @return {string}
     */
    requestParams() {
      return qs.stringify({
        ...this.collection.urlParams,
        ...this.config.requestQueryString, // Additional server params passed from table php file
        ...this.dataRequestQueryString,
      })
    },

    /**
     * Check if the search input is disabled
     *
     * @return {Boolean}
     */
    searchDisabled() {
      return this.isLoading && this.search.length > 0
    },

    /**
     * Count total columns
     *
     * @return {Number}
     */
    totalColumns() {
      return this.visibleColumns.length
    },

    /**
     * Determine visible columns
     * @return {Array}
     */
    visibleColumns() {
      if (!this.config.columns) {
        return []
      }

      return this.config.columns.filter(
        column => (!column.hidden || column.hidden == false) && column.attribute
      )
    },

    /**
     * The path used for request based on the passed resource name
     *
     * @return {string}
     */
    computedUrlPath() {
      if (this.urlPath) {
        return this.urlPath
      }

      return '/' + this.resourceName + '/' + 'table'
    },
  },
  methods: {
    /**
     * Create new HTTP request
     *
     * @param {Boolean} viaUserSearch
     *
     * @return {Void}
     */
    request(viaUserSearch = false) {
      if (this.isLoading) {
        return
      }

      this.cancelPreviousRequest()

      this.loading(true)

      // Reset the current page as the search won't be accurate as there will
      // be offset on the query and if any results are found, won't be queried
      if (viaUserSearch && this.collection.currentPage !== 1) {
        this.collection.currentPage = 1
      }

      let params = qs.stringify({
        rules: this.rulesAreValid ? this.rules : null,
        q: this.search,
      })

      Innoclapps.request()
        .get(`${this.computedUrlPath}?${this.requestParams}&${params}`, {
          cancelToken: new CancelToken(
            token => (this.requestCancelToken = token)
          ),
        })
        .then(({ data }) => {
          this.collection.setState(data)
          this.configureWatchers()
          this.$emit('loaded', { empty: this.isEmpty })
        })
        .finally(() => {
          this.loading(false)
          if (!this.initialDataLoaded) {
            // Add a little timeout so if there is no record and empty state
            // exists the table is not shown together with the empty state then hidden
            setTimeout(() => (this.initialDataLoaded = true), 150)
          }
        })
    },

    /**
     * Configure the component necessary watched
     * @return {Void}
     */
    configureWatchers() {
      if (this.watchersInitialized) {
        return
      }

      this.watchersInitialized = true

      this.$watch('requestParams', function (newVal, oldVal) {
        this.request()
      })

      this.$watch('config.perPage', function (newVal) {
        this.collection.perPage = Number(newVal)
      })

      this.$watch(
        'config.order',
        function (newVal) {
          // Sometimes when fast switching through tables
          // the order is undefined
          if (newVal) {
            this.collection.set('order', newVal)
          }
        },
        {
          deep: true,
        }
      )
    },

    /**
     * Prepare the component
     * @param  {Object} settings
     * @return {Void}
     */
    prepareComponent(settings) {
      this.collection.perPage = Number(settings.perPage)
      this.collection.set('order', settings.order)

      // Set the watchers after the inital data setup
      // This helps to immediately trigger watcher change|new value before setting the data
      this.$nextTick(() => {
        if (this.hasRules) {
          // Configure the watchers for filters, the filters will update the data
          // and the watchers will catch the change in requestParams to invoke the request
          this.configureWatchers()
        } else {
          this.request()
        }
        this.componentReady = true
      })
    },

    /**
     * Fetch the table settings
     *
     * @param {Boolean} force Indicates whether to force fetching the settings instead
     * of using directly from the store if they are already fetched
     *
     * @return {Void}
     */
    async fetchSettings(force = false) {
      let settings = await this.$store.dispatch('table/getSettings', {
        resourceName: this.resourceName,
        params: this.dataRequestQueryString,
        id: this.tableId,
        force: force,
      })

      return settings
    },

    /**
     * Re-fetch the table actions
     *
     * @return {Array}
     */
    async refetchActions() {
      let actions = await this.$store.dispatch('table/fetchActions', {
        resourceName: this.resourceName,
        params: this.dataRequestQueryString,
        id: this.tableId,
      })

      return actions
    },

    /**
     * Register the table reload listener
     *
     * @return {Void}
     */
    registerReloaders() {
      Innoclapps.$on(`${this.resourceName}-record-updated`, this.request)
      Innoclapps.$on('action-executed', this.actionExecutedRefresher)
      Innoclapps.$on('reload-resource-table', this.tableIdRefresher)
    },
    actionExecutedRefresher(action) {
      if (action.resourceName === this.resourceName) {
        this.request()
      }
    },
    tableIdRefresher(id) {
      if (id === this.tableId) {
        this.request()
      }
    },
    /**
     * Create the table data
     *
     * @return {Void}
     */
    handleMountedLifeCycle() {
      this.registerReloaders()
      this.fetchSettings().then(settings => this.prepareComponent(settings))
    },

    /**
     * Checks if there is previous request and cancel it
     * @return {Void}
     */
    cancelPreviousRequest() {
      if (!this.requestCancelToken) {
        return
      }

      this.requestCancelToken()
    },

    /**
     * Apply filters
     *
     * @param {Object} rules
     *
     * @return {Void}
     */
    applyFilters(rules) {
      // Wait till Vuex is updated
      this.$nextTick(this.request)
    },
  },

  /**
   * Handle the component mounted lifecycle hook
   *
   * @return {Void}
   */
  mounted() {
    this.handleMountedLifeCycle()
  },

  /**
   * Handle the component unmounted lifecycle hook
   *
   * @return {Void}
   */
  unmounted() {
    this.cancelPreviousRequest()
    this.collection.flush()
    this.loading(false)
    Innoclapps.$off(`${this.resourceName}-record-updated`, this.request)
    Innoclapps.$off('reload-resource-table', this.tableIdRefresher)
    Innoclapps.$off('action-executed', this.actionExecutedRefresher)
  },
}
</script>
