<template>
  <textarea
    :id="id"
    :name="name"
    :tabindex="tabindex"
    :autocomplete="autocomplete"
    :autofocus="autofocus"
    :required="required"
    :placeholder="placeholder"
    :pattern="pattern"
    :wrap="wrap"
    :minlength="minlength"
    :maxlength="maxlength"
    :rows="rows"
    :cols="cols"
    :disabled="disabled"
    v-model="localValue"
    @blur="blurHandler"
    @focus="focusHandler"
    @keyup="keyupHandler"
    @keydown="keydownHandler"
    @input="inputHandler"
    :class="{
      'resize-none overflow-y-hidden': resizeable,
      'border-neutral-300 dark:border-neutral-500': bordered,
      'border-transparent': !bordered,
    }"
    class="form-textarea rounded-md dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400"
  ></textarea>
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
    'change',
    'keyup',
    'keydown',
  ],
  props: {
    rows: [String, Number],
    cols: [String, Number],
    wrap: {
      type: String,
      default: 'soft',
    },
    bordered: {
      type: Boolean,
      default: true,
    },
    resizeable: {
      type: Boolean,
      default: true,
    },
    // When resizeable
    minHeight: {
      default: 60,
      type: [String, Number],
    },
  },
  data: () => ({
    clearTimeout: null,
  }),
  methods: {
    resizeTextarea(event) {
      event.target.style.height = 'auto'
      event.target.style.height = event.target.scrollHeight + 'px'
    },
    inputHandler(e) {
      if (this.resizeable) {
        this.resizeTextarea(e)
      }
      this.$emit('input', e.target.value)
    },
  },
  mounted() {
    if (this.resizeable) {
      this.$nextTick(() => {
        this.clearTimeout = setTimeout(() => {
          this.$el.setAttribute(
            'style',
            'height:' + (this.$el.scrollHeight || this.minHeight) + 'px;'
          )
        }, 400)
      })
    }
  },
  beforeUnmount() {
    this.clearTimeout && clearTimeout(this.clearTimeout)
  },
}
</script>
