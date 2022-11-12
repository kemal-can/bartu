/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export default {
  computed: {
    /**
     * Indicates whether all rows are selected
     *
     * @return {Boolean}
     */
    allRowsSelected() {
      return this.selectedRowsCount === this.collection.items.length
    },

    /**
     * Get a count of the selected rows
     *
     * @return {Number}
     */
    selectedRowsCount() {
      return this.selectedRows.length
    },

    /**
     * Get the selected rows
     *
     * @return {Array}
     */
    selectedRows() {
      return this.collection.items.filter(row => row.tSelected)
    },

    /**
     * Indicates whether the table is selectable
     *
     * @return {Boolean}
     */
    isSelectable() {
      if (this.collection.items.length === 0) {
        return false
      }

      if (
        this.config.hasCustomActions !== null &&
        this.config.hasCustomActions
      ) {
        return true
      }

      return this.config.actions.length > 0
    },
  },
  methods: {
    /**
     * Select row on table row click
     */
    selectOnRowClick(e, row) {
      if (!this.isSelectable) {
        return
      }

      // Only works when there is at least one row selected
      if (this.selectedRowsCount === 0) {
        return
      }

      if (
        e.target.tagName == 'INPUT' ||
        e.target.tagName == 'SELECT' ||
        e.target.tagName == 'TEXTAREA' ||
        e.target.isContentEditable ||
        e.target.tagName == 'A' ||
        e.target.tagName == 'BUTTON'
      ) {
        return
      }

      this.onRowSelected(row)
    },

    /**
     * On row selected event handler
     *
     * @param  {Object} row   The selected row object

     * @return {Void}
     */
    onRowSelected(row) {
      row.tSelected = !row.tSelected
    },

    /**
     * Toggle all rows
     *
     * @return {Void}
     */
    toggleSelectAll() {
      if (this.allRowsSelected) {
        this.unselectAllInternal()
        return
      }

      this.collection.items.forEach(row => (row.tSelected = true))
    },

    /**
     * Unselect all rows
     *
     * @see toggleSelectAll
     *
     * @return {Void}
     */
    unselectAllInternal() {
      this.collection.items.forEach(row => (row.tSelected = false))
    },
  },
}
