/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import HtmlInput from './HtmlInput'
export default {
  mixins: [HtmlInput],

  props: {
    modelValue: [String, Number],
    autocomplete: String,
    maxlength: [String, Number],
    minlength: [String, Number],
    pattern: String,
    placeholder: String,
  },

  data() {
    return {
      localValue: this.modelValue,
      valueWhenFocus: null,
    }
  },

  watch: {
    localValue(localValue) {
      this.$emit('update:modelValue', localValue)
    },
    modelValue(value) {
      this.localValue = value
    },
  },

  methods: {
    blurHandler(e) {
      this.$emit('blur', e)

      if (this.localValue !== this.valueWhenFocus) {
        this.$emit('change', this.localValue)
      }
    },

    focusHandler(e) {
      this.$emit('focus', e)

      this.valueWhenFocus = this.localValue
    },

    keyupHandler(e) {
      this.$emit('keyup', e)
    },

    keydownHandler(e) {
      this.$emit('keydown', e)
    },

    blur() {
      this.$el.blur()
    },

    click() {
      this.$el.click()
    },

    focus(options) {
      this.$el.focus(options)
    },

    select() {
      this.$el.select()
    },

    setRangeText(replacement) {
      this.$el.setRangeText(replacement)
    },
  },
}
