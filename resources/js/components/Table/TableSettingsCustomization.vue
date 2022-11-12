<template>
  <i-modal
    :id="tableId + 'listSettings'"
    size="sm"
    :description="$t('table.customize_list_view')"
    :title="$t('table.list_settings')"
    @hidden="updateVisibilityInStore(false)"
    @shown="updateVisibilityInStore(true)"
  >
    <div class="mb-4 mt-10" v-if="config.allowDefaultSortChange">
      <p
        class="mb-1.5 font-medium text-neutral-700 dark:text-neutral-100"
        v-t="'table.default_sort'"
      />
      <draggable
        v-model="sorted"
        :item-key="item => item.attribute + '-' + item.direction"
        handle=".sort-draggable-handle"
        v-bind="draggableOptions"
      >
        <template #item="{ element, index }">
          <div
            class="mb-1 flex items-center rounded border border-neutral-200 px-2 py-1 dark:border-neutral-600"
          >
            <div class="grow p-1">
              <i-form-select
                v-model="sorted[index].attribute"
                :id="'column_' + index"
              >
                <!-- ios by default selects the first field but no events are triggered in this case
                we will make sure to add blank one -->
                <option value="" v-if="!sorted[index].attribute"></option>
                <option
                  v-for="sortableColumn in sortable"
                  :key="sortableColumn.attribute"
                  :value="sortableColumn.attribute"
                  v-show="!isSortedColumnDisabled(sortableColumn.attribute)"
                >
                  {{ sortableColumn.label }}
                </option>
              </i-form-select>
            </div>
            <div class="p-1">
              <i-form-select
                v-model="sorted[index].direction"
                :id="'column_type_' + index"
              >
                <option value="asc">
                  Asc (<span v-t="'app.ascending'"></span>)
                </option>
                <option value="desc">
                  Desc (<span v-t="'app.descending'"></span>)
                </option>
              </i-form-select>
            </div>
            <div class="p-1">
              <i-button
                :variant="index === 0 ? 'secondary' : 'danger'"
                :disabled="index === 0 && isAddSortColumnDisabled"
                size="sm"
                @click="index === 0 ? addSortedColumn() : removeSorted(index)"
              >
                <icon icon="Plus" class="h-4 w-4" v-if="index === 0" />
                <icon icon="Minus" class="h-4 w-4" v-else-if="index > 0" />
              </i-button>
            </div>
            <div class="p-1">
              <i-button-icon
                icon="Selector"
                class="sort-draggable-handle cursor-move"
              />
            </div>
          </div>
        </template>
      </draggable>
    </div>

    <p
      class="mb-1.5 font-medium text-neutral-700 dark:text-neutral-100"
      v-t="'table.columns'"
    />
    <input-search v-model="search" @input="setTableConfig" />
    <div class="mt-4 overflow-auto" style="max-height: 400px">
      <draggable
        v-model="customizeableColumns"
        handle=".column-draggable-handle"
        :move="onColumnMove"
        item-key="attribute"
        v-bind="scrollableDraggableOptions"
      >
        <template #item="{ element }">
          <div
            class="mb-2 mr-2 flex rounded-md border border-neutral-200 py-2 px-3 dark:border-neutral-600"
          >
            <div class="grow">
              <i-form-checkbox
                v-i-tooltip="
                  element.primary === true ? $t('table.primary_column') : ''
                "
                v-model:checked="visibleColumns"
                :name="'col-' + element.attribute"
                :disabled="element.primary === true"
                :value="element.attribute"
                :id="'col-' + element.attribute"
              >
                <icon
                  icon="QuestionCircle"
                  class="h-4 w-4 text-neutral-600"
                  v-if="element.helpText"
                  v-i-tooltip="element.helpText"
                />
                {{ element.label }}
              </i-form-checkbox>
            </div>
            <div>
              <i-button-icon
                icon="Selector"
                class="column-draggable-handle cursor-move"
                v-show="!element.primary"
              />
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <hr class="my-3 border-t border-neutral-200 dark:border-neutral-600" />
    <i-form-group
      :label="$t('table.per_page')"
      label-for="tableSettingsPerPage"
    >
      <i-form-select v-model="perPage" id="tableSettingsPerPage">
        <option
          v-for="perPage in [25, 50, 100]"
          :key="perPage"
          :value="perPage"
        >
          {{ perPage }}
        </option>
      </i-form-select>
    </i-form-group>
    <i-form-group
      :label="$t('table.max_height')"
      :description="$t('table.max_height_info')"
      label-for="tableSettingsMaxHeight"
    >
      <div class="relative mt-1 rounded-md shadow-sm">
        <i-form-input
          type="number"
          min="200"
          step="50"
          class="pr-10"
          list="maxHeight"
          id="tableSettingsMaxHeight"
          v-model="maxHeight"
        />

        <datalist id="maxHeight">
          <option value="200" />
          <option value="250" />
          <option value="300" />
          <option value="350" />
          <option value="400" />
          <option value="500" />
        </datalist>
        <div
          class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
        >
          <span class="-mt-1 text-neutral-400">px</span>
        </div>
      </div>
    </i-form-group>
    <template #modal-footer>
      <div class="space-x-2 text-right">
        <i-button variant="white" @click="hideModal" size="sm">{{
          $t('app.cancel')
        }}</i-button>
        <i-button variant="white" @click="reset" size="sm">{{
          $t('app.reset')
        }}</i-button>
        <i-button variant="primary" @click="save" size="sm">{{
          $t('app.save')
        }}</i-button>
      </div>
    </template>
  </i-modal>
</template>
<script>
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
import ProvidesSettingsData from './ProvidesSettingsData'
import draggable from 'vuedraggable'
import filter from 'lodash/filter'
import find from 'lodash/find'
import Form from '@/components/Form/Form'

export default {
  inheritAttrs: false,
  mixins: [ProvidesDraggableOptions, ProvidesSettingsData],
  components: { draggable },
  data: () => ({
    sorted: [],
    customizeableColumns: [],
    visibleColumns: [],
    search: null,
    maxHeight: null,
    perPage: null,
  }),
  watch: {
    visible: function (newVal) {
      newVal ? this.showModal() : this.hideModal()
    },
  },
  computed: {
    /**
     * Indicates whether the customize settings section is visible
     */
    visible() {
      return this.$store.state.table.customize[this.tableId]
    },

    /**
     * Check whether the add column button is disabled
     * @return {Boolean}
     */
    isAddSortColumnDisabled() {
      if (this.sorted.length === this.sortable.length) {
        return true
      }

      // Do not allow the user to add new column before selecting a column from the latest added
      // Causing error with draggable and index/keys
      let notSelectedColumns = filter(
        this.sorted,
        column => column.attribute == ''
      )
      return notSelectedColumns.length > 0 ? true : false
    },

    /**
     * Only sortable columns
     *
     * @return {array}
     */
    sortable() {
      return filter(this.customizeableColumns, column => column.sortable)
    },

    /**
     * Count the total sortable columns
     *
     * @return {Number}
     */
    totalSortable() {
      return this.sortable.length
    },
  },
  methods: {
    /**
     * Update the visibility in store
     *
     * @param  {Boolean} data
     */
    updateVisibilityInStore(value) {
      this.$store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
        value: value,
        id: this.tableId,
      })
    },

    /**
     * On draggable columns move handler
     *
     * @param  {Object} data
     *
     * @return {null|Boolean}
     */
    onColumnMove(data) {
      // You can't reorder primary columns
      // you can't add new columns before the first primary column
      // as the first primary column contains specific data table related to the table
      // You can't add new columns after the last primary column
      if (
        this.customizeableColumns[data.draggedContext.index].primary ||
        (data.draggedContext.futureIndex === 0 &&
          this.customizeableColumns[data.draggedContext.futureIndex].primary) ||
        (data.draggedContext.futureIndex === this.totalSortable - 1 &&
          this.customizeableColumns[data.draggedContext.futureIndex].primary)
      ) {
        return false
      }
    },

    /**
     * Check wheter sorted column is disabled
     *
     * @param  {String}  attribute
     *
     * @return {Boolean}
     */
    isSortedColumnDisabled(attribute) {
      return Boolean(find(this.sorted, ['attribute', attribute]))
    },

    /**
     * Add new sortable column
     */
    addSortedColumn() {
      this.sorted.push({
        attribute: '',
        direction: 'asc',
      })
    },

    /**
     * Remove sorted column
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removeSorted(index) {
      this.sorted.splice(index, 1)
    },

    /**
     * Set the table defaults data based from the config
     */
    setDefaults() {
      this.setTableConfig().then(() => this.setDefaultVisibleColumns())
    },

    /**
     * Set the default visible columns from the server-side options
     */
    setDefaultVisibleColumns() {
      this.visibleColumns = []
      this.customizeableColumns.forEach(
        (column, index) =>
          column.hidden !== true && this.visibleColumns.push(column.attribute)
      )
    },
    /**
     * Reset table customized data
     *
     * @return {Void}
     */
    reset() {
      this.request(new Form()).then(this.initializeComponent)
    },

    /**
     * Table table customization
     *
     * @return {Void}
     */
    save() {
      this.request(
        new Form({
          // Remove any empty columns which the user used to add them via the + button but didn't selected a column
          order: filter(this.sorted, column => column.attribute != ''),
          columns: this.customizeableColumns.map((column, index) => ({
            attribute: column.attribute,
            order: index + 1,
            hidden: !this.visibleColumns.includes(column.attribute),
          })),
          maxHeight: this.maxHeight,
          perPage: this.perPage,
        })
      )
    },

    /**
     * Make the request to save the customized data
     *
     * @param  {Object} form
     *
     * @return {Promise}
     */
    async request(form) {
      await form.post(`${this.urlPath}/settings`).then(data => {
        this.$store.commit('table/UPDATE_SETTINGS', {
          id: this.tableId,
          settings: data,
        })

        // We will re-query the table because the hidden columns are not queried
        // and in this case the data won't be shown
        this.$nextTick(() => this.$iTable.reload(this.tableId))
        this.hideModal()
      })
    },

    /**
     * Show the customize modal
     *
     * @return {Void}
     */
    showModal() {
      this.$iModal.show(this.tableId + 'listSettings')
    },

    /**
     * Hide the customize modal
     *
     * @return {Void}
     */
    hideModal() {
      this.$iModal.hide(this.tableId + 'listSettings')
    },

    /**
     * Set the table config data
     *
     * @return {Promise}
     */
    async setTableConfig() {
      this.maxHeight = this.config.maxHeight
      this.perPage = this.config.perPage

      // Filter only columns that has ID to be available as customizeable columns
      this.customizeableColumns = filter(this.config.columns, column => {
        if (!column.attribute) {
          return false
        }

        if (this.search) {
          return column.label.toLowerCase().includes(this.search.toLowerCase())
        }

        return true
      })
    },

    /**
     * Initialize the component
     */
    initializeComponent() {
      this.setDefaults()
      this.sorted = this.cleanObject(this.config.order)
    },
  },
  created() {
    this.initializeComponent()
  },
}
</script>
