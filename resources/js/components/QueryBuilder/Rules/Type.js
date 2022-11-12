/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { needsArray } from '../Utils'
export default {
  inheritAttrs: false,
  props: {
    operand: {
      required: true,
    },
    isNullable: {
      required: true,
      type: Boolean,
    },
    index: {
      required: true,
      type: Number,
    },
    query: {
      type: Object,
      required: false,
    },
    rule: {
      type: Object,
      required: true,
    },
    labels: {
      required: true,
    },
    operator: {
      required: true,
    },
    isBetween: {
      default: false,
      type: Boolean,
    },
    readOnly: {
      default: false,
      type: Boolean,
    },
  },
  watch: {
    'query.operand': function (newVal, oldVal) {
      // reset the value when the operand changes as the operands
      // may be changed multiple times after the rule is added and the value may not match the newly selected operand
      // e.q. prevously was operand of type date and after change is text
      if (oldVal) {
        this.updateValue(needsArray(this.operator) ? [] : '')
      }
    },
    /**
     * Watch the operator for changes
     */
    operator: function (newVal, oldVal) {
      const nowNeedsArray = needsArray(newVal)
      // If now needs array and the current value is not array
      // set the current value to empty array
      if (nowNeedsArray && !Array.isArray(this.query.value)) {
        this.updateValue([])
      }

      // 1. If previous operator needed array and now don't need array just reset the value,
      // 2. If oldVal is "is" or "was" then set the value to empty as if it's date, will throw error for invalid date
      // 3. When selecting nullable operator, set the value only to empty
      if (
        (needsArray(oldVal) ||
          oldVal == 'is' ||
          oldVal == 'was' ||
          this.isNullable) &&
        !nowNeedsArray
      ) {
        this.updateValue('')
      }
    },
  },
  methods: {
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
    },
  },
}
