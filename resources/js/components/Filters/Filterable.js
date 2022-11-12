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
     * Get the active filter
     *
     * @return {null|Object}
     */
    activeFilter() {
      return this.$store.getters['filters/getActive'](
        this.filtersIdentifier,
        this.filtersView
      )
    },

    /**
     * Indicates whether the rules in the query builder are valid
     *
     * @return {Boolean}
     */
    rulesAreValid() {
      return Boolean(
        this.$store.getters['filters/rulesAreValid'](
          this.filtersIdentifier,
          this.filtersView
        )
      )
    },

    /**
     * Indicates whether there are rules applied in the query builder
     *
     * @return {Boolean}
     */
    hasRulesApplied() {
      return this.$store.getters['filters/hasRulesApplied'](
        this.filtersIdentifier,
        this.filtersView
      )
    },

    /**
     * Whether the resource has available rules/filters
     *
     * @return {Boolean}
     */
    hasRules() {
      return this.$store.getters['filters/hasRules'](this.filtersIdentifier)
    },

    /**
     * Indicates whether the filters rules are visible
     *
     * @return {Boolean}
     */
    rulesAreVisible() {
      return this.$store.getters['filters/rulesAreVisible'](
        this.filtersIdentifier,
        this.filtersView
      )
    },

    /**
     * Provides the current QB rules
     *
     * @return {Object}
     */
    rules() {
      return this.$store.getters['filters/getBuilderRules'](
        this.filtersIdentifier,
        this.filtersView
      )
    },
  },
  methods: {
    /**
     * Toggle the filters rules visibility
     *
     * @return {Void}
     */
    toggleFiltersRules() {
      this.$store.dispatch('filters/toggleFiltersRules', {
        identifier: this.filtersIdentifier,
        view: this.filtersView,
      })
    },
  },
  unmounted() {
    this.$store.commit('filters/RESET_BUILDER_RULES', {
      identifier: this.filtersIdentifier,
      view: this.filtersView,
    })
    this.$store.commit('filters/SET_RULES_VISIBLE', {
      identifier: this.filtersIdentifier,
      view: this.filtersView,
      value: false,
    })
  },
}
