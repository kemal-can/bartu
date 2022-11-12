<script>
import SelectField from '@/components/UI/CustomSelect'
export default {
  extends: SelectField,
  props: {
    filterable: {
      type: Boolean,
      default: false,
    },
    clearable: {
      type: Boolean,
      default: false,
    },
    taggable: {
      type: Boolean,
      default: true,
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    label: {
      type: String,
      default: 'address',
    },
    // Allow non matching addresse to be shown
    // based on the searched name (display_name)
    filterBy: {
      type: Function,
      default(option, label, search) {
        return (
          (label || '').toLowerCase().indexOf(search.toLowerCase()) > -1 ||
          (option.name || '').toLowerCase().indexOf(search.toLowerCase()) > -1
        )
      },
    },
  },
  computed: {
    /**
     * The currently displayed options, filtered
     * by the search elements value. If tagging
     * true, the search text will be prepended
     * if it doesn't already exist.
     *
     * @return {array}
     */
    filteredOptions() {
      // We will override the filteredOptions from i-custom-select
      // computed prop to make sure that the searched value is always
      // added as last, because when searching, the contacts, companies
      // should be shown on top to preveny any confusion and the search value
      // e.q. if searching by email to be as last so the user can select it if it's needed.

      const optionList = [].concat(this.optionList)
      if (!this.filterable && !this.taggable) {
        return optionList
      }

      let options = this.search.length
        ? this.filter(optionList, this.search, this)
        : optionList

      if (this.taggable && this.search.length) {
        const createdOption = this.createOption(this.search)
        if (!this.optionExists(createdOption)) {
          // options.unshift(createdOption);
          options.push(createdOption)
        }
      }
      return options
    },
  },

  methods: {
    // Override createOption method
    createOption(option) {
      option = {
        name: '',
        [this.label]: option,
      }

      this.$emit('option:created', option)

      return option
    },
    // Create unique key from the search results
    // As if we use ID, the ID can be duplicate e.q. for contact
    // and company uses the same ID
    getOptionKey(option) {
      return String(
        option.address + option.id + option.resourceName + option.name
      )
    },

    /**
     * Determine if two option objects are matching.
     *
     * @param a {Object}
     * @param b {Object}
     * @returns {boolean}
     */
    optionComparator(a, b) {
      // For invalid addresses handler
      if (typeof a == 'string' && typeof b === 'object') {
        return a === b.address
      } else if (typeof b == 'string' && typeof a === 'object') {
        return b === a.address
      }

      return this.getOptionKey(a) === this.getOptionKey(b)
    },
  },
}
</script>
<style scoped>
.cs__search::-webkit-search-cancel-button {
  display: none !important;
}

.cs__search::-webkit-search-decoration,
.cs__search::-webkit-search-results-button,
.cs__search::-webkit-search-results-decoration,
.cs__search::-ms-clear {
  display: none !important;
}

.cs__search,
.cs__search:focus {
  appearance: none !important;
}
</style>
