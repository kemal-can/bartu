<template>
  <div
    :class="['field-col mt-3 mb-1', { 'd-none': field.displayNone }, colWidth]"
  >
    <h5
      class="text-base font-medium text-neutral-800 dark:text-white"
      :class="[field.titleIcon ? 'inline-flex items-center' : '']"
    >
      <icon
        :icon="field.titleIcon"
        class="mr-1.5 h-4 w-4 text-current"
        v-if="field.titleIcon"
      />
      <span v-text="field.title"></span>
    </h5>
    <p
      v-html="field.message"
      class="text-sm text-neutral-600 dark:text-neutral-300"
    />
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    field: { type: Object, require: true },
  },
  computed: {
    /**
     * Get the column wrapper width
     *
     * @return {Number|null}
     */
    colWidth() {
      if (this.field.colClass === false) {
        return
      }

      return this.field.colClass || 'col-span-12'
    },
  },
  created() {
    this.field.fill = () => {}
    this.field.handleChange = () => {}
    this.field.isDirty = () => {
      return false
    }
  },
}
</script>
