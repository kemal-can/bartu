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
  props: {
    /**
     * Toggles the adding of a 'loading' class to the main
     * .v-select wrapper. Useful to control UI state when
     * results are being processed through AJAX.
     */
    loading: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      mutableLoading: false,
    }
  },

  watch: {
    /**
     * Anytime the search string changes, emit the
     * 'search' event. The event is passed with two
     * parameters: the search string, and a function
     * that accepts a boolean parameter to toggle the
     * loading state.
     *
     * @emits search
     */
    search() {
      this.$emit('search', this.search, this.toggleLoading)
    },

    /**
     * Sync the loading prop with the internal mutable loading value.
     */
    loading(val) {
      this.mutableLoading = val
    },
  },

  methods: {
    /**
     * Toggle this.loading. Optionally pass a boolean
     * value. If no value is provided, this.loading
     * will be set to the opposite of it's current value.
     *
     * @param {Boolean|null} toggle
     *
     * @returns {Boolean}
     */
    toggleLoading(toggle = null) {
      if (toggle == null) {
        return (this.mutableLoading = !this.mutableLoading)
      }

      return (this.mutableLoading = toggle)
    },
  },
}
