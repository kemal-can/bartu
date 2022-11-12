<template>
  <i-custom-select
    :input-id="inputId"
    :clearable="false"
    :model-value="modelValue"
    @option:selected="handleChange($event)"
    :options="formats"
  >
    <template #option="option">
      {{ option.label + ' [' + formatLabel(option) + ']' }}
    </template>
  </i-custom-select>
</template>
<script>
import store from '@/store'

export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      type: String,
      default() {
        return store.state.settings.date_format
      },
    },
    inputId: {
      type: String,
      default: 'date_format',
    },
  },
  data: () => ({
    formats: Innoclapps.config.date_formats,
  }),
  methods: {
    handleChange(value) {
      this.$emit('update:modelValue', value)
    },
    formatLabel(option) {
      return moment().formatPHP(option.label)
    },
  },
  mounted() {
    // Emit the initial value in case it's only taken from the configuration to fill the form
    this.handleChange(this.modelValue)
  },
}
</script>
