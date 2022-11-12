<template>
  <div class="inline-block max-h-80 w-full overflow-auto">
    <i-dropdown-item
      @click="$emit('click', filter.id)"
      v-for="filter in filters"
      :key="filter.id"
      :active="active && active.id == filter.id"
      :text="filter.name"
      :icon="isDefault(filter) ? 'Star' : null"
      prepend-icon
    />
  </div>
</template>
<script>
export default {
  emits: ['click'],
  props: {
    filters: { type: Array, required: true },
    identifier: { required: true, type: String },
    view: { required: true, type: String },
  },
  computed: {
    /**
     * Provides the active filter
     *
     * @return {null|Object}
     */
    active() {
      return this.$store.getters['filters/getActive'](
        this.identifier,
        this.view
      )
    },

    /**
     * Get the default filters
     *
     * @return {Objet|null}
     */
    default() {
      return this.$store.getters['filters/getDefault'](
        this.identifier,
        this.view,
        this.currentUser.id
      )
    },
  },
  methods: {
    /**
     * Check whether the given filter is default for the current view
     *
     * @param {Object} filter
     *
     * @return {Boolean}
     */
    isDefault(filter) {
      if (!this.default) {
        return false
      }

      return filter.id == this.default.id
    },
  },
}
</script>
