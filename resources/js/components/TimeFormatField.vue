<template>
  <i-form-select
    :id="inputId"
    :model-value="modelValue"
    @change="handleChange($event)"
  >
    <option v-for="format in formats" :key="format" :value="format">
      {{ format }} [<span v-once v-text="formatForDisplay(format)"></span>]
    </option>
  </i-form-select>
</template>
<script>
import store from '@/store'

export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      type: String,
      default() {
        return store.state.settings.time_format
      },
    },
    inputId: {
      type: String,
      default: 'time_format',
    },
  },
  data: () => ({
    formats: Innoclapps.config.time_formats,
  }),
  methods: {
    handleChange(value) {
      this.$emit('update:modelValue', value)
    },
    formatForDisplay(value) {
      return moment().formatPHP(value)
    },
  },
  mounted() {
    // Emit the initial value in case it's only taken from the configuration to fill the form
    this.handleChange(this.modelValue)
  },
}
</script>
