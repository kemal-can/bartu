<template>
  <td :class="['whitespace-nowrap', item.tdClass ? item.tdClass : null]">
    <slot v-bind="slotProps"></slot>
  </td>
</template>
<script>
export default {
  props: {
    field: { type: Object, required: true },
    item: { type: Object, required: true },
    formatter: { type: Function, required: true },
  },
  computed: {
    /**
     * Get the data cell slot props
     *
     * @return {Object}
     */
    slotProps() {
      return {
        row: this.item,
        column: this.field,
        formatted: this.formatted,
      }
    },

    /**
     * Get the formatted data cell text
     *
     * @return {String}
     */
    formatted() {
      return this.formatter(this.item, this.field)
    },
  },
}
</script>
