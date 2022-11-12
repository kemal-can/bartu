<template>
  <input
    :id="id"
    :name="name"
    :disabled="disabled"
    :autocomplete="autocomplete"
    :autofocus="autofocus"
    :type="type"
    :tabindex="tabindex"
    :required="required"
    :placeholder="placeholder"
    :pattern="pattern"
    :minlength="minlength"
    :maxlength="maxlength"
    :min="min"
    :max="max"
    v-model="localValue"
    @blur="blurHandler"
    @focus="focusHandler"
    @keyup="keyupHandler"
    @keydown="keydownHandler"
    @input="inputHandler"
    :class="[
      'form-input dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
      {
        'form-input-sm': size === 'sm',
        'form-input-lg': size === 'lg',
        rounded: rounded && size === 'sm',
        'rounded-md': rounded && size !== 'sm' && size !== false,
        'border-neutral-300 dark:border-neutral-500': bordered,
        'border-transparent': !bordered,
      },
    ]"
  />
</template>
<script>
import TextInput from './TextInput'
export default {
  mixins: [TextInput],
  emits: [
    'update:modelValue',
    'focus',
    'blur',
    'input',
    'keyup',
    'keydown',
    'change',
  ],
  props: {
    rounded: {
      default: true,
      type: Boolean,
    },
    bordered: {
      default: true,
      type: Boolean,
    },
    size: {
      type: [String, Boolean],
      default: '',
      validator(value) {
        return ['sm', 'lg', 'md', '', false].includes(value)
      },
    },
    type: {
      type: String,
      default: 'text',
    },
    max: {
      type: [String, Number],
      default: undefined,
    },
    min: {
      type: [String, Number],
      default: undefined,
    },
  },
  methods: {
    inputHandler(e) {
      this.$emit('input', e.target.value)
    },
  },
}
</script>
