<template>
  <td
    v-bind="cellAttributes"
    :class="[
      'whitespace-normal',
      row.tSelected && '!bg-neutral-50 dark:!bg-neutral-800',
    ]"
  >
    <div :class="{ flex: shouldContainSelectCheckbox }">
      <i-form-checkbox
        v-if="shouldContainSelectCheckbox"
        class="mr-4"
        @click="$emit('selected', row)"
        :checked="row.tSelected"
      />

      <slot :row="row" :column="column" :formatted="formatted">
        {{ formatted }}
      </slot>
    </div>
  </td>
</template>
<script>
import get from 'lodash/get'
export default {
  emits: ['selected'],
  props: {
    column: { required: true, type: Object },
    row: { required: true, type: Object },
    isSelectable: { required: true, type: Boolean },
    index: { required: true, type: Number },
  },
  computed: {
    /**
     * Indicates whether the column has select checkbox
     *
     * @return {Boolean}
     */
    shouldContainSelectCheckbox() {
      return this.isSelectable && this.index === 0
    },

    /**
     * Get the table cell data attributes
     *
     * @return {Object}
     */
    cellAttributes() {
      return {
        style: {
          width: this.column.width,
          'min-width': this.column.minWidth,
        },
        class: [
          this.column.tdClass,
          {
            'is-primary table-sticky-column': this.column.primary === true,
          },
        ],
      }
    },

    /**
     * Get the formatted value
     *
     * lodash get does deep dot notiation search too, used for relations
     *
     * @return {mixed}
     */
    formatted() {
      if (this.column.relationField) {
        // Allow as well manually specifying path via the attribute e.q. relation.attribute
        return (
          get(this.row, 'displayAs.' + this.column.attribute) ||
          get(
            this.row,
            !this.column.attribute.includes('.')
              ? this.column.attribute + '.' + this.column.relationField
              : this.column.attribute
          )
        )
      }

      return (
        get(this.row, 'displayAs.' + this.column.attribute) ||
        get(this.row, this.column.attribute)
      )
    },
  },
}
</script>
