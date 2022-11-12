<template>
  <!--  Todo for multiple check if valueKey and labelKey will work -->
  <i-custom-select
    :multiple="isMultiSelect"
    v-model="selectValue"
    :disabled="readOnly"
    size="sm"
    :input-id="'rule-' + rule.id + '-' + index"
    :placeholder="placeholder"
    :label="rule.labelKey"
    :options="options"
  >
  </i-custom-select>
</template>
<script>
import Type from './Type'
import InteractsWithOptions from '@/mixins/InteractsWithOptions'
import find from 'lodash/find'
import isEqual from 'lodash/isEqual'
import { isValueEmpty } from '@/utils'
export default {
  mixins: [Type, InteractsWithOptions],
  data: () => ({
    selectValue: null,
  }),
  watch: {
    /**
     * Watch the value for change and update actual query value
     *
     * @type {mixed}
     */
    selectValue: {
      handler: function (newVal, oldVal) {
        this.handleChange(newVal)
      },
      deep: true,
    },
  },
  methods: {
    /**
     * Handle change for select to update the value
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    handleChange(option) {
      let value = null

      if (option && !isValueEmpty(option[this.rule.valueKey])) {
        // Allows zero in the value
        value = option[this.rule.valueKey]
      }

      this.updateValue(value)
    },

    /**
     * Initialize component
     *
     * @param  {array} options
     *
     * @return {Void}
     */
    initializeComponent(options) {
      this.setOptions(options)

      // First option selected by default
      if (isValueEmpty(this.query.value)) {
        this.updateValue(this.options[0][this.rule.valueKey])
      } else {
        this.setInitialValue()
      }
    },

    /**
     * Set the select initial internal value
     */
    setInitialValue() {
      let value = find(this.options, [this.rule.valueKey, this.query.value])

      this.updateValue(value ? value[this.rule.valueKey] : null)
    },

    /**
     * Set the select value from the given query builder value
     *
     * @param  {Mixed} value
     *
     * @return {Void}
     */
    setSelectValue(value) {
      if (isValueEmpty(value)) {
        this.selectValue = null
        return
      }

      this.selectValue = find(this.options, [this.rule.valueKey, value]) || null
    },

    /**
     * Update the current rule query value
     *
     * @param  {Mixed} value
     *
     * @return {Void}
     */
    updateValue(value) {
      this.$store.commit('filters/UPDATE_QUERY_VALUE', {
        query: this.query,
        value: value,
      })

      if (!isEqual(value, this.selectValue)) {
        this.setSelectValue(value)
      }
    },
  },
  computed: {
    /**
     * Get the select placeholder
     */
    placeholder() {
      const key = this.isMultiSelect
        ? 'filters.placeholders.choose_with_multiple'
        : 'filters.placeholders.choose'

      return this.$t(key, {
        label: this.operand ? this.operand.label : this.rule.label,
      })
    },

    /**
     * Check whether the select is multiple
     *
     * @return {Boolean}
     */
    isMultiSelect() {
      return this.rule.type === 'multi-select'
    },
  },
  created() {
    this.getOptions(this.rule).then(options =>
      this.initializeComponent(options)
    )
  },
}
</script>
