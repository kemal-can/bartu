<template>
  <div>
    <div
      class="flex flex-col items-center justify-between py-3 px-7 md:flex-row"
    >
      <table-per-page-options
        :collection="collection"
        class="mb-2 md:mb-0"
        @change="loadItems"
        :disabled="loading"
      />
      <div class="w-full md:w-auto">
        <input-search
          v-model="search"
          @input="performSearch"
          :disabled="loading"
        />
      </div>
    </div>
    <i-overlay :show="loading">
      <i-table :id="tableId" v-bind="tableProps" ref="table">
        <thead>
          <tr>
            <header-cell
              v-for="field in fields"
              :key="'th-' + field.key"
              :ref="'th-' + field.key"
              :class="{
                hidden: stacked[field.key],
              }"
              :field="field"
              v-model:ctx="ctx"
              @update:ctx="loadItems"
            />
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in collection.items"
            :class="[item.trClass ? item.trClass : null]"
            :key="'tr-' + item.id"
          >
            <data-cell
              v-for="field in fields"
              :key="'td-' + field.key"
              :field="field"
              :item="item"
              :formatter="dataCellFormatter"
              :class="{
                hidden: stacked[field.key],
              }"
            >
              <template v-slot="slotProps">
                <slot v-bind="slotProps" :name="field.key">
                  <span v-if="field.key === fields[0].key && item.path">
                    <router-link class="link" :to="item.path">{{
                      slotProps.formatted
                    }}</router-link>
                  </span>
                  <span v-else v-text="slotProps.formatted"> </span>
                </slot>
                <!-- Stacked -->
                <template v-if="field.key === fields[0].key">
                  <stacked-data-cell
                    v-for="stackedField in stackedFields"
                    :key="'stacked-' + stackedField.key"
                    :field="stackedField"
                    :item="item"
                    :formatter="dataCellFormatter"
                  >
                    <template v-slot="stackedSlotProps">
                      <slot v-bind="stackedSlotProps" :name="stackedField.key">
                        <span class="text-neutral-700 dark:text-neutral-300">
                          {{ stackedSlotProps.formatted }}
                        </span>
                      </slot>
                    </template>
                  </stacked-data-cell>
                </template>
              </template>
            </data-cell>
          </tr>
          <tr v-if="!collection.hasItems">
            <td :colspan="totalFields">
              <slot
                name="empty"
                :text="emptyText"
                :loading="loading"
                :search="search"
              >
                {{ emptyText }}
              </slot>
            </td>
          </tr>
        </tbody>
      </i-table>
    </i-overlay>
    <table-pagination
      :collection="collection"
      class="px-7 py-3"
      :loading="loading"
    />
  </div>
</template>
<script>
const qs = require('qs')
import Paginator from '@/services/ResourcePaginator'
import TablePagination from '@/components/Table/TablePagination'
import TablePerPageOptions from '@/components/Table/TablePerPageOptions'
import { CancelToken } from '@/services/HTTP'
import debounce from 'lodash/debounce'
import { isResponsiveTableColumnVisible } from '@/utils'
import { clearCache as clearIsVisibleCache } from '@/utils/isResponsiveTableColumnVisible'
import HeaderCell from './TableSimpleHeaderCell.vue'
import DataCell from './TableSimpleDataCell.vue'
import StackedDataCell from './TableSimpleStackedDataCell.vue'
export default {
  emits: ['data-loaded'],
  components: {
    TablePagination,
    TablePerPageOptions,
    HeaderCell,
    DataCell,
    StackedDataCell,
  },
  props: {
    stackable: Boolean,
    tableProps: {
      type: Object,
      default() {
        return {}
      },
    },
    requestUri: { required: true, type: String },
    requestQueryString: Object,
    tableId: { required: true, type: String },
    actionColumn: Boolean,
    initialData: Object,
    fields: Array,
    // Initial sort by field key/name
    sortBy: String,
  },
  data: () => ({
    loading: false,
    search: '',
    initialDataSet: false,
    requestCancelToken: null,
    collection: new Paginator(),
    replaceCollectionData: null,
    stacked: {},
    ctx: {
      sortBy: null,
      direction: null,
    },
  }),
  watch: {
    'collection.currentPage': function (newVal, oldVal) {
      this.loadItems()
    },
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

      if (this.loading) {
        return '...'
      }

      if (this.search) {
        return this.$t('app.no_search_results')
      }

      return this.$t('table.empty')
    },

    /**
     * Get the stacked fields
     */
    stackedFields() {
      return this.fields.filter(field => this.stacked[field.key])
    },

    /**
     * Get the total number of columns of the table
     *
     * @return {Number}
     */
    totalFields() {
      return this.fields.length
    },

    /**
     * Get the query string for request
     *
     * @return {String}
     */
    queryString() {
      return {
        page: this.collection.currentPage,
        per_page: this.collection.perPage,
        q: this.search,
        order: [
          {
            field: this.ctx.sortBy || this.sortBy,
            direction: this.ctx.direction || 'asc',
          },
        ],
        ...(this.requestQueryString || {}),
      }
    },
  },

  methods: {
    /**
     * Perform search request
     *
     * @param  {String} value
     *
     * @return {Void}
     */
    performSearch: debounce(function () {
      this.loadItems()
    }, 400),

    /**
     * Format the given item for data cell
     *
     * @param  {Object} item
     * @param  {Object} field
     *
     * @return {String}
     */
    dataCellFormatter(item, field) {
      return field.formatter
        ? field.formatter(item[field.key], field.key, item)
        : item[field.key]
    },

    /**
     * Replace the table data with the given
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    replaceCollection(data) {
      this.replaceCollectionData = data
      this.reload()
    },

    /**
     * Reload the table
     *
     * @return {Void}
     */
    reload() {
      this.loadItems()
    },

    /**
     * Make table request
     *
     * @return {Void}
     */
    request() {
      this.cancelPreviousRequest()

      let { queryString } = this
      this.loading = true
      Innoclapps.request()
        .get(`/${this.requestUri}?${qs.stringify(queryString)}`, {
          cancelToken: new CancelToken(
            token => (this.requestCancelToken = token)
          ),
        })
        .then(({ data }) => {
          // cards support data.items
          this.collection.setState(data.items ? data.items : data)

          this.$emit('data-loaded', {
            items: this.collection.items,
            requestQueryString: queryString,
          })

          this.stackable && this.$nextTick(this.stackColumns)
        })
        .finally(() => (this.loading = false))
    },

    /**
     * Load table items
     *
     * @return {Void}
     */
    loadItems() {
      if (!this.initialDataSet && this.initialData) {
        this.initialDataSet = true
        this.collection.setState(this.initialData)
        this.stackable && this.$nextTick(this.stackColumns)
      } else if (this.replaceCollectionData !== null) {
        this.collection.setState(this.replaceCollectionData)
        this.replaceCollectionData = null
        this.stackable && this.$nextTick(this.stackColumns)
      } else {
        this.request()
      }
    },

    /**
     * Checks if there is previous request and cancel it
     *
     * @return {Void}
     */
    cancelPreviousRequest() {
      if (this.requestCancelToken) {
        this.requestCancelToken()
      }
    },

    /**
     * Stack the columns
     *
     * @return {Void}
     */
    stackColumns() {
      this.fields.forEach((field, idx) => {
        if (idx > 0 && this.$refs['th-' + field.key]) {
          const headerCell = this.$refs['th-' + field.key][0]
          this.stacked[field.key] = !isResponsiveTableColumnVisible(
            headerCell.$el,
            this.$refs.table.$el
          )
        }
      })
    },
  },

  /**
   * Handle the component created lifecycle
   *
   * @return {Void}
   */
  created() {
    this.loadItems()
  },

  /**
   * Handle the component mounted lifecycle
   *
   * @return {Void}
   */
  mounted() {
    this.stackable && window.addEventListener('resize', this.stackColumns)
  },

  /**
   * Handle the component before unmount lifecycle
   *
   * @return {Void}
   */
  beforeUnmount() {
    this.stackable && window.removeEventListener('resize', this.stackColumns)
  },

  /**
   * Handle the component unmounted lifecycle
   *
   * @return {Void}
   */
  unmounted() {
    clearIsVisibleCache()
    this.collection.flush()
  },
}
</script>
