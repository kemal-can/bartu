<template>
  <i-dropdown
    :placement="placement"
    class="min-w-0 max-w-full sm:max-w-xs"
    ref="dropdown"
    icon="Filter"
    no-caret
    v-bind="$attrs"
  >
    <template #toggle-content>
      <span class="truncate">
        {{ !active ? $t('filters.filters') : active.name }}
      </span>
    </template>

    <div class="w-[19rem]">
      <div class="px-4 py-3">
        <a
          href="#"
          @click.prevent="initiateNewFilter"
          :class="[
            'link mr-2 text-sm',
            {
              'border-r border-neutral-300 pr-2 dark:border-neutral-700':
                active,
            },
          ]"
          v-t="'filters.new'"
        />

        <a
          v-show="active"
          href="#"
          class="link border-r border-neutral-300 pr-2 text-sm dark:border-neutral-700"
          @click.prevent="initiateEdit"
          v-t="'filters.edit'"
        />

        <a
          href="#"
          v-show="active"
          class="link pl-2 text-sm"
          @click.prevent="clearActive"
          v-t="'filters.clear_applied'"
        />

        <input-search
          v-model="search"
          class="mt-3"
          :placeholder="$t('filters.search')"
        />
      </div>

      <p
        v-show="hasSavedFilters && !searchResultIsEmpty"
        class="block inline-flex items-center truncate px-4 py-2 text-sm font-medium text-neutral-900 dark:text-neutral-200"
      >
        <icon icon="ViewList" class="mr-2 h-5 w-5 text-current" />
        {{ $t('filters.available') }}
      </p>

      <p
        v-show="!hasSavedFilters || searchResultIsEmpty"
        class="block px-4 py-2 text-center text-sm text-neutral-500 dark:text-neutral-300"
        v-t="'filters.not_available'"
      />

      <filters-dropdown-items
        :identifier="identifier"
        :view="view"
        v-show="hasSavedFilters && !searchResultIsEmpty"
        :filters="filteredList"
        @click="handleFilterSelected"
      />
    </div>
  </i-dropdown>
</template>
<script>
import FiltersDropdownItems from './FiltersDropdownItems'

export default {
  inheritAttrs: false,
  emits: ['apply'],
  components: { FiltersDropdownItems },
  props: {
    placement: { default: 'bottom-end', type: String },
    identifier: { required: true, type: String },
    view: { required: true, type: String },
  },
  data: () => ({
    search: null,
  }),
  computed: {
    /**
     * Indicates whether the search results are empty
     *
     * @return {Boolean}
     */
    searchResultIsEmpty() {
      return this.search && this.filteredList.length === 0
    },

    /**
     * Get the filterd list for the dropdown
     *
     * @return {Array}
     */
    filteredList() {
      if (!this.search) {
        return this.filters
      }

      return this.filters.filter(filter => {
        return filter.name.toLowerCase().includes(this.search.toLowerCase())
      })
    },

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
     * Check whether there is saved filters for the resource
     *
     * @return {Boolean}
     */
    hasSavedFilters() {
      return this.filters.length > 0
    },

    /**
     * Saved filters ordered by name
     *
     * @return {Array}
     */
    filters() {
      return this.$store.getters['filters/getAll'](this.identifier)
    },

    /**
     * Currently filter rules in the builder
     *
     * @type {Object}
     */
    rules() {
      return this.$store.getters['filters/getBuilderRules'](
        this.identifier,
        this.view
      )
    },
  },
  methods: {
    /**
     * Hide the dropdown
     */
    hide() {
      this.$refs.dropdown.hide()
    },

    /**
     * Clear the current active filter
     *
     * @return {Void}
     */
    clearActive() {
      this.$store.dispatch('filters/clearActive', {
        view: this.view,
        identifier: this.identifier,
      })

      this.$emit('apply', this.rules)
    },

    /**
     * Edit the active filter
     *
     * @return {Void}
     */
    initiateEdit() {
      this.toggleFiltersRules()
      this.hide()
    },

    /**
     * Initiate new filter
     *
     * @return {Void}
     */
    initiateNewFilter() {
      if (this.active) {
        this.clearActive()
      }

      this.$store.commit('filters/SET_RULES_VISIBLE', {
        view: this.view,
        visible: true,
        identifier: this.identifier,
      })

      this.hide()
    },

    /**
     * Handle filter selected
     *
     * Emits to global filters selected event
     *
     * @param {Number} id
     *
     * @return {Void}
     */
    handleFilterSelected(id) {
      Innoclapps.$emit(`${this.identifier}-${this.view}-filter-selected`, id)
    },

    /**
     * Toggle the filters rules visibility
     *
     * @return {Void}
     */
    toggleFiltersRules() {
      this.$store.dispatch('filters/toggleFiltersRules', {
        identifier: this.identifier,
        view: this.view,
      })
    },
  },
}
</script>
