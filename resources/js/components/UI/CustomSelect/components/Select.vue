<template>
  <i-dropdown
    ref="select"
    popper-class="bg-white rounded-md dark:bg-neutral-800"
    :auto-size="true"
    @hide="dropdownHiddenEventHandler"
    @show="dropdownShownEventHandler"
    v-bind="$attrs"
  >
    <template #toggle>
      <div
        ref="toggle"
        :class="[
          {
            'border-primary-500 ring-1 ring-primary-500': open,
            'pointer-events-none bg-neutral-200': disabled,
            'px-2.5 py-1.5': size === 'sm',
            'px-3 py-2': size === 'md' || size === '',
            'px-4 py-2.5': size === 'lg',
            'border border-neutral-300 dark:border-neutral-500': bordered,
            rounded: rounded && size === 'sm',
            'rounded-md': rounded && size !== 'sm' && size !== false,
          },
          'relative w-full bg-white text-left text-sm shadow-sm dark:bg-neutral-700',
          stateClasses,
        ]"
      >
        <div class="flex items-center">
          <div
            :id="`cs${uid}__combobox`"
            class="flex items-center"
            :class="[{ 'cursor-text': searchable }]"
            role="combobox"
            :aria-expanded="dropdownOpen.toString()"
            :aria-owns="`cs${uid}__listbox`"
            aria-label="Search for an option"
          >
            <div
              v-show="!search || multiple"
              :class="{
                'absolute left-auto flex opacity-40': !multiple && dropdownOpen,
              }"
            >
              <slot
                v-for="option in selectedValue"
                name="selected-option-container"
                :option="normalizeOptionForSlot(option)"
                :deselect="deselect"
                :multiple="multiple"
                :disabled="disabled"
              >
                <selected-option
                  :option="option"
                  :get-option-label="getOptionLabel"
                  :get-option-key="getOptionKey"
                  :normalize-option-for-slot="normalizeOptionForSlot"
                  :multiple="multiple"
                  :searching="searching"
                  :disabled="disabled"
                  :deselect="deselect"
                >
                  <template #option="slotProps">
                    <slot name="selected-option" v-bind="slotProps">
                      {{ slotProps.optionLabel }}
                    </slot>
                  </template>
                </selected-option>
              </slot>
            </div>
          </div>
          <div class="mr-4 grow">
            <input
              :class="[
                dropdownOpen || isValueEmpty || searching ? '!w-full' : '!w-0',
                'max-w-full border-0 p-0 text-sm leading-none focus:border-0 focus:ring-0 disabled:bg-neutral-200 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
              ]"
              v-bind="{
                disabled: disabled,
                placeholder: searchPlaceholder,
                tabindex: tabindex,
                readonly: !searchable,
                class: 'cs__search',
                id: inputId,
                'aria-autocomplete': 'list',
                'aria-labelledby': `cs${uid}__combobox`,
                'aria-controls': `cs${uid}__listbox`,
                ref: 'search',
                type: 'search',
                autocomplete: autocomplete,
                value: search,
                ...(dropdownOpen && filteredOptions[typeAheadPointer]
                  ? {
                      'aria-activedescendant': `cs${uid}__option-${typeAheadPointer}`,
                    }
                  : {}),
              }"
              @blur="onSearchBlur"
              @focus="onSearchFocus"
              @input="search = $event.target.value"
              @compositionstart="isComposing = true"
              @compositionend="isComposing = true"
              @keydown.delete="maybeDeleteValue"
              @keydown.esc="onEscape"
              @keydown.prevent.up="typeAheadUp"
              @keydown.prevent.down="typeAheadDown"
              @keydown.prevent.enter="!isComposing && typeAheadSelect()"
            />
          </div>
          <div class="inline-flex">
            <button
              type="button"
              v-show="showClearButton"
              :disabled="disabled"
              @click.prevent.stop="clearSelection"
              class="mr-1 text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
              title="Clear Selected"
              aria-label="Clear Selected"
              ref="clearButton"
            >
              <icon
                icon="X"
                :class="[
                  'shrink-0 text-neutral-400',
                  size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4',
                ]"
              />
            </button>

            <slot name="spinner" v-bind="{ loading: mutableLoading }">
              <i-spinner
                :class="[
                  'mr-2 shrink-0 text-neutral-400',
                  size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4',
                ]"
                v-if="mutableLoading"
              />
            </slot>

            <div>
              <icon
                icon="Selector"
                :class="[
                  'text-neutral-400',
                  size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4',
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </template>
    <ul
      :id="`cs${uid}__listbox`"
      :key="`cs${uid}__listbox`"
      ref="dropdownMenu"
      class="max-h-80 overflow-y-auto"
      role="listbox"
      tabindex="-1"
    >
      <select-option
        v-for="(option, index) in filteredOptions"
        :option="option"
        :key="index"
        :index="index"
        :type-ahead-pointer="typeAheadPointer"
        :uid="uid"
        :selectable="selectable"
        :get-option-label="getOptionLabel"
        :get-option-key="getOptionKey"
        :select="select"
        :is-option-selected="isOptionSelected"
        :normalize-option-for-slot="normalizeOptionForSlot"
        @typeAheadPointer="typeAheadPointer = $event"
      >
        <template #option="slotProps">
          <slot name="option" v-bind="slotProps">
            {{ slotProps.optionLabel }}
          </slot>
        </template>
      </select-option>
      <li
        v-if="totalFilteredOptions === 0"
        class="relative cursor-default select-none py-2 px-3 text-center text-sm text-neutral-700 dark:text-neutral-300"
      >
        <slot
          name="no-options"
          v-bind="{ search: search, loading: loading, searching: searching }"
        >
          {{ $t('app.no_search_results') }}
        </slot>
      </li>
    </ul>
  </i-dropdown>
</template>
<script>
import pointerScroll from '../mixins/pointerScroll'
import typeAheadPointer from '../mixins/typeAheadPointer'
import ajax from '../mixins/ajax'
import input from '../mixins/input'
import sortAndStringify from '../utility/sortAndStringify'
import uniqueId from '../utility/uniqueId'
import SelectOption from './Option'
import SelectedOption from './SelectedOption'
import props from '../props'

export default {
  inheritAttrs: false,
  name: 'ICustomSelect',
  emits: [
    'update:modelValue',
    'open',
    'close',
    'cleared',
    'option:selecting',
    'option:created',
    'option:selected',
    'option:deselecting',
    'option:deselected',
    'search',
    'search:blur',
    'search:focus',
  ],
  components: { SelectOption, SelectedOption },
  mixins: [pointerScroll, typeAheadPointer, ajax, input],

  props: props,

  data() {
    return {
      uid: uniqueId(),
      search: '',
      open: false,
      isComposing: false,
      pushedTags: [],
      _value: [], // Internal value managed if no `modelValue` prop is passed
    }
  },

  watch: {
    /**
     * Maybe reset the value when options change.
     * Make sure selected option is correct.
     */
    options(newVal, oldVal) {
      let shouldReset = () =>
        typeof this.resetOnOptionsChange === 'function'
          ? this.resetOnOptionsChange(newVal, oldVal, this.selectedValue)
          : this.resetOnOptionsChange

      if (!this.taggable && shouldReset()) {
        this.clearSelection()
      }

      if (this.modelValue && this.isTrackingValues) {
        this.setInternalValueFromOptions(this.modelValue)
      }
    },

    /**
     * Make sure to update internal value if prop changes outside
     */
    modelValue(newVal) {
      if (this.isTrackingValues) {
        this.setInternalValueFromOptions(newVal)
      }
    },

    /**
     * Always reset the value when the multiple prop changes.
     */
    multiple() {
      this.clearSelection()
    },

    /**
     * Emits open/close events when the open data property changes
     *
     * @emits open
     * @emits close
     */
    open(isOpen) {
      this.$emit(isOpen ? 'open' : 'close')
    },
  },

  methods: {
    /**
     * Local create option function
     *
     * @param  {Mixed} option
     *
     * @return {Object}
     */
    createOption(option) {
      if (this.createOptionProvider) {
        return this.createOptionProvider(option)
      }

      return typeof this.optionList[0] === 'object'
        ? { [this.label]: option }
        : option
    },

    /**
     * Callback to filter results when search text
     * is provided. Default implementation loops
     * each option, and returns the result of
     * this.filterBy.
     *
     * @type   {Function}
     *
     * @param  {Array} list of options
     * @param  {String} search text
     *
     * @return {Boolean}
     */
    filter(options, search) {
      return options.filter(option => {
        let label = this.getOptionLabel(option)
        if (typeof label === 'number') {
          label = label.toString()
        }
        return this.filterBy(option, label, search)
      })
    },

    /**
     * Callback to generate the label text. If {option}
     * is an object, returns option[this.label] by default.
     *
     * Label text is used for filtering comparison and
     * displaying. If you only need to adjust the
     * display, you should use the `option` and
     * `selected-option` slots.
     *
     * @type {Function}
     *
     * @param  {Object|String} option
     *
     * @return {String}
     */
    getOptionLabel(option) {
      if (this.optionLabelProvider) {
        return this.optionLabelProvider(option)
      }

      if (typeof option === 'object') {
        if (!option.hasOwnProperty(this.label)) {
          return console.warn(
            `[vue-select warn]: Label key "option.${this.label}" does not` +
              ` exist in options object ${JSON.stringify(option)}.\n` +
              'https://vue-select.org/api/props.html#getoptionlabel'
          )
        }
        return option[this.label]
      }
      return option
    },

    /**
     * Generate a unique identifier for each option. If `option`
     * is an object and `option.hasOwnProperty('id')` exists,
     * `option.id` is used by default, otherwise the option
     * will be serialized to JSON.
     *
     * If you are supplying a lot of options, you should
     * provide your own keys, as JSON.stringify can be
     * slow with lots of objects.
     *
     * The result of this function *must* be unique.
     *
     * @type {Function}
     *
     * @param  {Object|String} option
     *
     * @return {String}
     */
    getOptionKey(option) {
      if (typeof option !== 'object') {
        return option
      }

      try {
        return option.hasOwnProperty('id')
          ? option.id
          : sortAndStringify(option)
      } catch (e) {
        const warning =
          `[vue-select warn]: Could not stringify this option ` +
          `to generate unique key. Please provide'getOptionKey' prop ` +
          `to return a unique key for each option.\n` +
          'https://vue-select.org/api/props.html#getoptionkey'
        return console.warn(warning, option, e)
      }
    },

    /**
     * Make sure tracked value is one option if possible.
     *
     * @param  {Object|String} value
     *
     * @return {void}
     */
    setInternalValueFromOptions(value) {
      if (Array.isArray(value)) {
        this._value = value.map(val => this.findOptionFromReducedValue(val))
      } else {
        this._value = this.findOptionFromReducedValue(value)
      }
    },

    /**
     * Select a given option.
     *
     * @emits option:selecting
     * @emits option:created
     * @emits option:selected
     *
     * @param  {Object|String} option
     *
     * @return {void}
     */
    select(option) {
      this.$emit('option:selecting', option)
      if (!this.isOptionSelected(option)) {
        if (this.taggable && !this.optionExists(option)) {
          this.$emit('option:created', option)
          this.pushTag(option)
        }
        if (this.multiple) {
          option = this.selectedValue.concat(option)
        }
        this.updateValue(option)
        this.$emit('option:selected', option)
      }
      this.onAfterSelect(option)
    },

    /**
     * De-select a given option.
     *
     * @emits option:deselecting
     * @emits option:deselected
     *
     * @param  {Object|String} option
     *
     * @return {Void}
     */
    deselect(option) {
      this.$emit('option:deselecting', option)
      this.updateValue(
        this.selectedValue.filter(val => {
          return !this.optionComparator(val, option)
        })
      )
      this.$emit('option:deselected', option)
    },

    /**
     * Clears the currently selected value(s)
     *
     * @emits cleared
     *
     * @return {Void}
     */
    clearSelection() {
      this.updateValue(this.multiple ? [] : null)
      this.$emit('cleared')
    },

    /**
     * Called from this.select after each selection.
     *
     * @param  {Object|String} option
     *
     * @return {Void}
     */
    onAfterSelect(option) {
      if (this.closeOnSelect) {
        this.$refs.select.hide()
        // this.open = !this.open
        this.searchEl.blur()
      }

      if (this.clearSearchOnSelect) {
        this.search = ''
      }
    },

    /**
     * Accepts a selected value, updates local state when required, and triggers the input event.
     *
     * @emits update:modelValue
     *
     * @param {Mixed} value
     */
    updateValue(value) {
      if (typeof this.modelValue === 'undefined') {
        // Vue select has to manage value
        this._value = value
      }

      if (value !== null) {
        if (Array.isArray(value)) {
          value = value.map(val => this.reduce(val))
        } else {
          value = this.reduce(value)
        }
      }

      this.$emit('update:modelValue', value)
    },

    /**
     * Handle the dropdown shown event
     * We need to focus the search element when the dropdown is invoked via the toggle e.q. clicked not on the search input
     *
     * @return {Void}
     */
    dropdownShownEventHandler() {
      this.open = true
      this.searchEl.focus()
    },

    /**
     * Handle the dropdown hidden event
     *
     * @return {Void}
     */
    dropdownHiddenEventHandler() {
      this.open = false
    },

    /**
     * Check if the given option is currently selected.
     *
     * @param  {Object|String}  option
     *
     * @return {Boolean}
     */
    isOptionSelected(option) {
      return this.selectedValue.some(value =>
        this.optionComparator(value, option)
      )
    },

    /**
     * Determine if two option objects are matching.
     *
     * @param a {Object}
     * @param b {Object}
     *
     * @return {Boolean}
     */
    optionComparator(a, b) {
      return this.getOptionKey(a) === this.getOptionKey(b)
    },

    /**
     * Finds an option from the options where a reduced value matches the passed in value.
     *
     * @param {Object} value
     *
     * @return {Mixed}
     */
    findOptionFromReducedValue(value) {
      const predicate = option =>
        JSON.stringify(this.reduce(option)) === JSON.stringify(value)

      const matches = [...this.options, ...this.pushedTags].filter(predicate)

      if (matches.length === 1) {
        return matches[0]
      }

      /**
       * This second loop is needed to cover an edge case where `taggable` + `reduce`
       * were used in conjunction with a `create-option` that doesn't create a
       * unique reduced value.
       *
       * @see https://github.com/sagalbot/vue-select/issues/1089#issuecomment-597238735
       */
      return (
        matches.find(match => this.optionComparator(match, this._value)) ||
        value
      )
    },

    /**
     * Delete the value on Delete keypress when there is no text in the search input, & there's tags to delete
     *
     * @return {Void}
     */
    maybeDeleteValue() {
      if (
        !this.searchEl.value.length &&
        this.selectedValue &&
        this.selectedValue.length &&
        this.clearable
      ) {
        let value = null
        if (this.multiple) {
          value = [
            ...this.selectedValue.slice(0, this.selectedValue.length - 1),
          ]
        }
        this.updateValue(value)
      }
    },

    /**
     * Determine if an option exists within this.optionList array.
     *
     * @param  {Object|String} option
     *
     * @return {Boolean}
     */
    optionExists(option) {
      return this.optionList.some(_option =>
        this.optionComparator(_option, option)
      )
    },

    /**
     * Ensures that options are always passed as objects to scoped slots.
     *
     * @param {String|Object} option
     *
     * @return {Object}
     */
    normalizeOptionForSlot(option) {
      return typeof option === 'object' ? option : { [this.label]: option }
    },

    /**
     * If push-tags is true, push the given option to `this.pushedTags`.
     *
     * @param  {Object|String} option
     *
     * @return {void}
     */
    pushTag(option) {
      this.pushedTags.push(option)
    },

    /**
     * If there is any text in the search input, remove it.
     * Otherwise, blur the search input to close the dropdown.
     *
     * @return {Void}
     */
    onEscape() {
      if (!this.search.length) {
        this.searchEl.blur()
      } else {
        this.search = ''
      }
    },

    /**
     * Close the dropdown on blur.
     *
     * @emits  {search:blur}
     *
     * @return {Void}
     */
    onSearchBlur(e) {
      // The floating-vue package is unfocusing the element on click, in this case, we need to re-focus
      // @see https://github.com/Akryum/floating-vue/commit/0a39097bb99da8f3e1fd6a9595fbf6468ea17a21
      if (
        e.relatedTarget &&
        e.relatedTarget.classList.contains('v-popper__popper')
      ) {
        this.searchEl.focus()
        return
      }

      this.$emit('search:blur')
    },

    /**
     * Open the dropdown on focus.
     *
     * @emits  {search:focus}
     *
     * @return {Void}
     */
    onSearchFocus() {
      this.$emit('search:focus')
    },
  },

  computed: {
    /**
     * Determine if the component needs to track the state of values internally.
     *
     * @return {Boolean}
     */
    isTrackingValues() {
      return typeof this.modelValue === 'undefined' || Boolean(this.reduce)
    },

    /**
     * The options that are currently selected.
     *
     * @return {Array}
     */
    selectedValue() {
      let value = this.modelValue
      if (this.isTrackingValues) {
        // Vue select has to manage value internally
        value = this._value
      }

      if (value) {
        return [].concat(value)
      }

      return []
    },

    /**
     * The options available to be chosen from the dropdown, including any tags that have been pushed.
     *
     * @return {Array}
     */
    optionList() {
      return this.options.concat(this.pushTags ? this.pushedTags : [])
    },

    /**
     * Find the search input DOM element.
     *
     * @return {HTMLInputElement}
     */
    searchEl() {
      return this.$refs.search
    },

    /**
     * Holds the current state of the component.
     *
     * @return {Object}
     */
    stateClasses() {
      return {
        'cs--open': this.dropdownOpen,
        'cs--single': !this.multiple,
        'cs--searching': this.searching,
        'cs--searchable': this.searchable,
        'cs--unsearchable': !this.searchable,
        'cs--loading': this.mutableLoading,
        'cs--disabled': this.disabled,
      }
    },

    /**
     * Return the current state of the search input
     *
     * @return {Boolean}
     */
    searching() {
      return !!this.search
    },

    /**
     * Return the current state of the dropdown menu.
     *
     * @return {Boolean}
     */
    dropdownOpen() {
      return this.open && !this.mutableLoading
    },

    /**
     * Return the placeholder string if it's set and there is no value selected.
     *
     * @return {String|null}
     */
    searchPlaceholder() {
      if (this.isValueEmpty && this.placeholder) {
        return this.placeholder
      }
    },

    /**
     * Get the total number of options in the select
     *
     * @return {Number}
     */
    totalFilteredOptions() {
      return this.filteredOptions.length
    },

    /**
     * The currently displayed options, filtered
     * by the search elements value. If tagging
     * true, the search text will be prepended
     * if it doesn't already exist.
     *
     * @return {Array}
     */
    filteredOptions() {
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
          options.unshift(createdOption)
        }
      }
      return options
    },

    /**
     * Check if there aren't any options selected.
     *
     * @return {Boolean}
     */
    isValueEmpty() {
      return this.selectedValue.length === 0
    },

    /**
     * Determines if the clear button should be displayed.
     *
     * @return {Boolean}
     */
    showClearButton() {
      return (
        !this.multiple && this.clearable && !this.open && !this.isValueEmpty
      )
    },
  },

  created() {
    this.mutableLoading = this.loading

    if (typeof this.modelValue !== 'undefined' && this.isTrackingValues) {
      this.setInternalValueFromOptions(this.modelValue)
    }
  },
}
</script>
<style scoped>
::v-deep(.v-popper) {
  width: 100% !important;
}

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
