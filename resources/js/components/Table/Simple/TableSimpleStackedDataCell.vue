<template>
  <p class="flex items-center font-normal">
    <span class="mr-1 font-medium text-neutral-800 dark:text-neutral-100"
      >{{ field.label }}:</span
    >
    <slot v-bind="slotProps"></slot>
  </p>
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
