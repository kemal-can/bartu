<template>
  <th class="group text-left">
    <a
      v-if="field.sortable"
      href="#"
      @click.prevent="toggleSortable"
      class="inline-flex w-full items-center hover:text-neutral-700 dark:hover:text-neutral-400"
    >
      {{ field.label }}
      <icon
        :icon="isSortedAscending ? 'ChevronUp' : 'ChevronDown'"
        :class="[
          'ml-1.5 h-3 w-3',
          isTableOrderedByCurrentField
            ? 'opacity-100'
            : 'opacity-0 group-hover:opacity-100',
        ]"
      />
    </a>
    <span v-else v-text="field.label" />
  </th>
</template>
<script>
export default {
  emits: ['update:ctx'],
  props: {
    field: { type: Object, required: true },
    ctx: { type: Object, required: true },
  },
  computed: {
    /**
     * Check whether the table is ordered by current field
     *
     * @return {Boolean}
     */
    isTableOrderedByCurrentField() {
      return this.ctx.sortBy === this.field.key
    },

    /**
     * Check whether current field is sorted ascending
     *
     * @return {Boolean}
     */
    isSortedAscending() {
      return this.ctx.direction === 'asc' && this.isTableOrderedByCurrentField
    },
  },
  methods: {
    /**
     * Toggle sortable column
     *
     * @return {Void}
     */
    toggleSortable() {
      const ctx = {}
      if (this.isTableOrderedByCurrentField) {
        ctx.sortBy = this.field.key
        ctx.direction = this.ctx.direction === 'desc' ? 'asc' : 'desc'
      } else {
        ctx.sortBy = this.field.key
        ctx.direction = 'desc'
      }

      this.$emit('update:ctx', Object.assign({}, this.ctx, ctx))
    },
  },
}
</script>
