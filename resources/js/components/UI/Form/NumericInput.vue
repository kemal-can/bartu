<template>
  <i-form-input
    :placeholder="placeholder"
    :disabled="disabled"
    @blur="onBlurHandler"
    @input="onInputHandler"
    @focus="onFocusHandler"
    v-model="amount"
    @change="onChangeHandler"
    v-bind="$attrs"
    ref="numeric"
    type="tel"
  >
  </i-form-input>
</template>

<script>
import accounting from 'accounting-js'

export default {
  inheritAttrs: false,
  emits: ['change', 'blur', 'focus', 'update:modelValue'],

  props: {
    /**
     * Currency symbol.
     */
    currency: {
      type: String,
      default: '',
      required: false,
    },

    /**
     * Maximum value allowed.
     */
    max: {
      type: Number,
      default: Number.MAX_SAFE_INTEGER || 9007199254740991,
      required: false,
    },

    /**
     * Minimum value allowed.
     */
    min: {
      type: Number,
      default: Number.MIN_SAFE_INTEGER || -9007199254740991,
      required: false,
    },

    /**
     * Enable/Disable minus value.
     */
    minus: {
      type: Boolean,
      default: false,
      required: false,
    },

    /**
     * Input placeholder.
     */
    placeholder: {
      type: String,
      default: '',
      required: false,
    },

    /**
     * Value when the input is empty
     */
    emptyValue: {
      type: [Number, String],
      default: '',
      required: false,
    },

    /**
     * Number of decimals.
     * Decimals symbol are the opposite of separator symbol.
     */
    precision: {
      type: Number,
      default() {
        return Number(Innoclapps.config.currency.precision)
      },
      required: false,
    },

    /**
     * Thousand separator type.
     * Separator props accept either . or , (default).
     */
    separator: {
      type: String,
      default: ',',
      required: false,
    },

    /**
     * Forced thousand separator.
     * Accepts any string.
     */
    thousandSeparator: {
      default() {
        return Innoclapps.config.currency.thousands_separator
      },
      required: false,
      type: String,
    },

    /**
     * Forced decimal separator.
     * Accepts any string.
     */
    decimalSeparator: {
      default() {
        return Innoclapps.config.currency.decimal_mark
      },
      required: false,
      type: String,
    },
    /**
     * The output type used for v-model.
     * It can either be String or Number (default).
     */
    outputType: {
      required: false,
      type: String,
      default: 'Number',
    },

    /**
     * v-model value.
     */
    modelValue: {
      type: [Number, String],
      default: '',
      required: true,
    },

    disabled: {
      type: Boolean,
      default: false,
      required: false,
    },

    /**
     * Position of currency symbol
     * Symbol position props accept either 'suffix' or 'prefix' (default).
     */
    currencySymbolPosition: {
      type: String,
      default: 'prefix',
      required: false,
    },
  },

  data: () => ({
    amount: '',
    clearTimeout: null,
  }),

  computed: {
    /**
     * Number type of formatted value.
     * @return {Number}
     */
    amountNumber() {
      return this.unformat(this.amount)
    },

    /**
     * Number type of value props.
     * @return {Number}
     */
    valueNumber() {
      return this.unformat(this.modelValue, '.')
    },

    /**
     * Define decimal separator based on separator props.
     * @return {String} '.' or ','
     */
    decimalSeparatorSymbol() {
      if (typeof this.decimalSeparator !== 'undefined')
        return this.decimalSeparator
      if (this.separator === ',') return '.'
      return ','
    },

    /**
     * Define thousand separator based on separator props.
     * @return {String} '.' or ','
     */
    thousandSeparatorSymbol() {
      if (typeof this.thousandSeparator !== 'undefined')
        return this.thousandSeparator
      if (this.separator === '.') return '.'
      if (this.separator === 'space') return ' '
      return ','
    },

    /**
     * Define format position for currency symbol and value.
     * @return {String} format
     */
    symbolPosition() {
      if (!this.currency) return '%v'
      return this.currencySymbolPosition === 'suffix' ? '%v %s' : '%s %v'
    },
  },

  watch: {
    /**
     * Watch for value change from other input with same v-model.
     * @param {Number} newValue
     */
    valueNumber(newValue) {
      if (this.$refs.numeric.$el !== document.activeElement) {
        this.amount = this.format(newValue)
      }
    },

    /**
     * Immediately reflect separator changes
     */
    separator() {
      this.process(this.valueNumber)
      this.amount = this.format(this.valueNumber)
    },

    /**
     * Immediately reflect currency changes
     */
    currency() {
      this.process(this.valueNumber)
      this.amount = this.format(this.valueNumber)
    },

    /**
     * Immediately reflect precision changes
     */
    precision() {
      this.process(this.valueNumber)
      this.amount = this.format(this.valueNumber)
    },
  },

  mounted() {
    // Set default value props when valueNumber has some value
    if (this.valueNumber || this.isDeliberatelyZero()) {
      this.process(this.valueNumber)
      this.amount = this.format(this.valueNumber)

      // In case of delayed props value.
      this.clearTimeout = setTimeout(() => {
        this.process(this.valueNumber)
        this.amount = this.format(this.valueNumber)
      }, 500)
    }
  },

  methods: {
    /**
     * Handle change event.
     * @param {Object} e
     */
    onChangeHandler(e) {
      this.$emit('change', e)
    },

    /**
     * Handle blur event.
     * @param {Object} e
     */
    onBlurHandler(e) {
      this.$emit('blur', e)
      this.amount = this.format(this.valueNumber)
    },

    /**
     * Handle focus event.
     * @param {Object} e
     */
    onFocusHandler(e) {
      this.$emit('focus', e)
      if (this.valueNumber === 0) {
        this.amount = null
      } else {
        this.amount = accounting.formatMoney(this.valueNumber, {
          symbol: '',
          format: '%v',
          thousand: '',
          decimal: this.decimalSeparatorSymbol,
          precision: Number(this.precision),
        })
      }
    },

    /**
     * Handle input event.
     */
    onInputHandler(e) {
      this.process(this.amountNumber)
    },

    /**
     * Validate value before update the component.
     * @param {Number} value
     */
    process(value) {
      if (value >= this.max) this.update(this.max)
      if (value <= this.min) this.update(this.min)
      if (value > this.min && value < this.max) this.update(value)
      if (!this.minus && value < 0)
        this.min >= 0 ? this.update(this.min) : this.update(0)
    },

    /**
     * Update parent component model value.
     * @param {Number} value
     */
    update(value) {
      const fixedValue = accounting.toFixed(value, this.precision)
      const output =
        this.outputType.toLowerCase() === 'string'
          ? fixedValue
          : Number(fixedValue)
      this.$emit('update:modelValue', output)
    },

    /**
     * Format value using symbol and separator.
     * @param {Number} value
     * @return {String}
     */
    format(value) {
      return accounting.formatMoney(value, {
        symbol: this.currency,
        format: this.symbolPosition,
        precision: Number(this.precision),
        decimal: this.decimalSeparatorSymbol,
        thousand: this.thousandSeparatorSymbol,
      })
    },

    /**
     * Remove symbol and separator.
     * @param {Number} value
     * @param {String} decimalSeparator
     * @return {Number}
     */
    unformat(value, decimalSeparator) {
      const toUnformat =
        typeof value === 'string' && value === '' ? this.emptyValue : value

      return accounting.unformat(
        toUnformat,
        decimalSeparator || this.decimalSeparatorSymbol
      )
    },

    /**
     * Check if value was deliberately set to zero and not just evaluated
     * @return {boolean}
     */
    isDeliberatelyZero() {
      return this.valueNumber === 0 && this.modelValue !== ''
    },
  },
  beforeUnmount() {
    this.clearTimeout && clearTimeout(this.clearTimeout)
  },
}
</script>
