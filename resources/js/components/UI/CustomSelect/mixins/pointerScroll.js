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
    autoscroll: {
      type: Boolean,
      default: true,
    },
  },

  watch: {
    typeAheadPointer() {
      if (this.autoscroll) {
        this.maybeAdjustScroll()
      }
    },
  },

  methods: {
    /**
     * Adjust the scroll position of the dropdown list if the current pointer is outside of the overflow bounds.
     *
     * @return {Number|null}
     */
    maybeAdjustScroll() {
      const optionEl =
        this.$refs.dropdownMenu?.children[this.typeAheadPointer] || false

      if (optionEl) {
        const bounds = this.getDropdownViewport()
        const { top, bottom, height } = optionEl.getBoundingClientRect()

        if (top < bounds.top) {
          return (this.$refs.dropdownMenu.scrollTop = optionEl.offsetTop)
        } else if (bottom > bounds.bottom) {
          return (this.$refs.dropdownMenu.scrollTop =
            optionEl.offsetTop - (bounds.height - height))
        }
      }
    },

    /**
     * The currently viewable portion of the dropdownMenu.
     *
     * @return {Object}
     */
    getDropdownViewport() {
      return this.$refs.dropdownMenu
        ? this.$refs.dropdownMenu.getBoundingClientRect()
        : {
            height: 0,
            top: 0,
            bottom: 0,
          }
    },
  },
}
