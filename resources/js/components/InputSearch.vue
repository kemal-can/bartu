<template>
  <div class="relative rounded-md shadow-sm">
    <div
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
    >
      <icon icon="SearchSolid" class="h-5 w-5 text-neutral-400" />
    </div>
    <i-form-input
      class="pl-10"
      v-bind="$attrs"
      type="search"
      :disabled="disabled"
      :model-value="modelValue"
      :id="inputId"
      @click="$emit('click', $event)"
      @keydown.enter="emitEvent($event)"
      @input="emitEvent($event)"
      :placeholder="placeholder || $t('app.search')"
    />
  </div>
</template>
<script>
import debounce from 'lodash/debounce'
export default {
  inheritAttrs: false,
  emits: ['click', 'input', 'update:modelValue'],
  props: {
    modelValue: {},
    inputId: String,
    placeholder: String,
    disabled: {
      default: false,
    },
  },
  methods: {
    /**
     * Emit the search event
     *
     * @param  {String|KeyboardEvent} value)
     *
     * @return {Void}
     */
    emitEvent: debounce(function (value) {
      if (value instanceof KeyboardEvent) {
        value = value.target.value
      }

      this.$emit('update:modelValue', value)
      this.$emit('input', value)
    }, 650),
  },
}
</script>
