<template>
  <select
    :id="id"
    :name="name"
    :autofocus="autofocus"
    :placeholder="placeholder"
    :tabindex="tabindex"
    :disabled="disabled"
    :required="required"
    :multiple="multiple"
    :class="[
      'form-select dark:bg-neutral-700 dark:text-white',
      {
        'form-select-sm': size === 'sm',
        'form-select-lg': size === 'lg',
        rounded: rounded && size === 'sm',
        'rounded-md': rounded && size !== 'sm',
        'border-neutral-300 dark:border-neutral-500': bordered,
        'border-transparent': !bordered,
      },
    ]"
    :value="modelValue"
    @blur="blurHandler"
    @focus="focusHandler"
    @input="inputHandler"
    @change="changeHandler"
  >
    <slot></slot>
  </select>
</template>
<script>
import HtmlInput from './HtmlInput'
export default {
  mixins: [HtmlInput],
  emits: ['update:modelValue', 'focus', 'blur', 'input', 'change'],
  props: {
    modelValue: {},
    placeholder: String,
    multiple: Boolean,
    size: {
      type: [String, Boolean],
      default: '',
      validator(value) {
        return ['sm', 'lg', '', true, false].includes(value)
      },
    },
    rounded: {
      default: true,
      type: Boolean,
    },
    bordered: {
      default: true,
      type: Boolean,
    },
  },
  methods: {
    changeHandler(e) {
      this.$emit('update:modelValue', e.target.value)
      this.$emit('change', e.target.value)
    },

    inputHandler(e) {
      this.$emit('input', e.target.value)
    },

    blurHandler(e) {
      this.$emit('blur', e)
    },

    focusHandler(e) {
      this.$emit('focus', e)
    },

    blur() {
      this.$el.blur()
    },

    focus(options) {
      this.$el.focus(options)
    },
  },
}
</script>
