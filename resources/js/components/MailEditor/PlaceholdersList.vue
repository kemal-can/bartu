<template>
  <div>
    <input-search v-model="search" />
    <div class="mt-4 flex flex-wrap">
      <div
        class="mr-1 mb-1 flex items-center rounded border border-neutral-200 py-1 px-3 dark:hover:border-neutral-400"
        v-for="placeholder in filteredPlaceholders"
        :key="groupName + placeholder.tag"
      >
        <a
          href="#"
          class="mr-1 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-200 dark:hover:text-neutral-400"
          @click.prevent="requestInsert(placeholder)"
          v-text="placeholder.description"
        >
        </a>
        <i-action-message
          v-show="
            justInsertedPlaceholder &&
            justInsertedPlaceholder.tag === placeholder.tag
          "
          message="Added!"
        />
      </div>
      <slot></slot>
    </div>
  </div>
</template>
<script>
export default {
  emits: ['insert-requested'],
  props: ['placeholders', 'groupName'],
  data: () => ({
    search: null,
    justInsertedPlaceholder: null,
  }),
  computed: {
    /**
     * Filtered placeholders based on the search value
     *
     * @return {Array}
     */
    filteredPlaceholders() {
      if (!this.search) {
        return this.placeholders
      }

      return this.placeholders.filter(
        placeholder =>
          placeholder.description
            .toLowerCase()
            .includes(this.search.toLowerCase()) ||
          placeholder.tag.toLowerCase().includes(this.search.toLowerCase())
      )
    },
  },
  methods: {
    /**
     * Request placeholder insert
     *
     * @param  {Object} placeholder
     *
     * @return {Void}
     */
    requestInsert(placeholder) {
      this.search = null
      this.justInsertedPlaceholder = placeholder
      this.$emit('insert-requested', placeholder)
      setTimeout(() => (this.justInsertedPlaceholder = null), 3000)
    },
  },
}
</script>
