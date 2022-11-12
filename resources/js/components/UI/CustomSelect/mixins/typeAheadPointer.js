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
  data() {
    return {
      typeAheadPointer: -1,
    }
  },

  watch: {
    filteredOptions() {
      for (let i = 0; i < this.filteredOptions.length; i++) {
        if (this.selectable(this.filteredOptions[i])) {
          this.typeAheadPointer = i
          break
        }
      }
    },
  },

  methods: {
    /**
     * Move the typeAheadPointer visually up the list by
     * setting it to the previous selectable option.
     *
     * @return {Void}
     */
    typeAheadUp() {
      for (let i = this.typeAheadPointer - 1; i >= 0; i--) {
        if (this.selectable(this.filteredOptions[i])) {
          this.typeAheadPointer = i
          break
        }
      }
    },

    /**
     * Move the typeAheadPointer visually down the list by
     * setting it to the next selectable option.
     *
     * @return {Void}
     */
    typeAheadDown() {
      for (
        let i = this.typeAheadPointer + 1;
        i < this.filteredOptions.length;
        i++
      ) {
        if (this.selectable(this.filteredOptions[i])) {
          this.typeAheadPointer = i
          break
        }
      }
    },

    /**
     * Select the option at the current typeAheadPointer position.
     * Optionally clear the search input on selection.
     *
     * @return {Void}
     */
    typeAheadSelect() {
      const typeAheadOption = this.filteredOptions[this.typeAheadPointer]

      if (typeAheadOption) {
        this.select(typeAheadOption)
      }
    },
  },
}
